import 'package:flutter/material.dart';
import 'httpUtils.dart';
import 'package:provider/provider.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'package:tuple/tuple.dart';
import 'moduleView.dart';

class ExternalDropdown extends StatefulWidget {
  const ExternalDropdown(
      {Key? key,
      required this.idField,
      required this.extModule,
      required this.curVal})
      : super(key: key);

  final String extModule;
  final String curVal;
  final String idField;

  @override
  State<ExternalDropdown> createState() =>
      _ExternalDropdownState(idField, extModule, curVal);
}

class _ExternalDropdownState extends State<ExternalDropdown> {
  List<DropdownMenuItem<String>> _items = [];
  String _selected = "";
  String _extModule = "";
  String _idField = "";

  _ExternalDropdownState(String idField, String mod, String curVal) {
    _idField = idField;
    _extModule = mod;
    _selected = curVal;
  }

  @override
  void initState() {
    super.initState();
    var cachedState = Provider.of<CachedState>(context, listen: false);
    String options = cachedState.getCachedModuleData(_extModule);
    if (options == "") {
      HttpUtils.queryApi(_extModule + "/fetch_ext").then((result) {
        setState(() {
          cachedState.cacheModuleData(_extModule, result);
          _items = _buildMenuItems(result);
        });
      });
    } else {
      setState(() {
        _items = _buildMenuItems(options);
      });
    }
  }

  List<DropdownMenuItem<String>> _buildMenuItems(String jsonOpt) {
    List<DropdownMenuItem<String>> menuItems = [];
    menuItems.add(const DropdownMenuItem(
      child: Text("None"),
      value: "",
    ));
    Map<int, String> parsedOptions = HttpUtils.parseExtJson(jsonOpt);
    parsedOptions.forEach((key, value) {
      menuItems.add(DropdownMenuItem(
        child: Text(value),
        value: key.toString(),
      ));
    });
    return menuItems;
  }

  void setSelected(String sel) {
    setState(() {
      _selected = sel;
    });
  }

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField(
      items: _items,
      value: _selected,
      onSaved: (value) {
        context
            .findAncestorStateOfType<_EditScreenState>()
            ?._savedValues[_idField.toLowerCase()] = value.toString();
      },
      onChanged: (String? newVal) {
        if (newVal != null && newVal != _selected) {
          setState(() {
            _selected = newVal;
          });
        }
        return;
      },
    );
  }
}

class EditScreen extends StatefulWidget {
  // In the constructor, require a Module.
  EditScreen({Key? key, required this.module, required this.id})
      : super(key: key);
  // Declare a field that holds the Module.
  final String module;
  final int id;

  @override
  _EditScreenState createState() => _EditScreenState(module, id);
}

class _EditScreenState extends State<EditScreen> {
  final _formKey = GlobalKey<FormState>();
  String module = "";
  int id = 0;
  Map<String, String> _savedValues = {};

  _EditScreenState(String mod, int i) {
    module = mod;
    id = i;
  }

  Map<String, Tuple4<String, String, String, String>> _updateDisplayData(
      String allModuleData) {
    var parsed = HttpUtils.getSingleItemFromJson(allModuleData, id);
    if (parsed.isEmpty) {
      parsed = HttpUtils.buildEmptyItemFromJson(allModuleData);
    }
    Map<String, Tuple4<String, String, String, String>> retVal = {};
    parsed.forEach((key, value) {
      Map<dynamic, dynamic> convertedValue = value as Map;
      if (!convertedValue.containsKey("header") ||
          !convertedValue.containsKey("edit_data")) {
        return;
      }
      if (convertedValue.containsKey("editable") &&
          !convertedValue["editable"]) {
        return;
      }
      String displayData = convertedValue["edit_data"] == null
          ? ""
          : convertedValue["edit_data"].toString();
      retVal[key] = Tuple4<String, String, String, String>(
          convertedValue["header"],
          displayData,
          convertedValue["type"] ?? "text",
          convertedValue["ext_module"] ?? "");
    });
    return retVal;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBar(title: Text(module), actions: <Widget>[
          Consumer<NetworkState>(
            builder: (context, value, child) {
              return Text(value.getConnectionString());
            },
          )
        ]),
        body: Consumer<CachedState>(
          builder: (context, value, child) {
            var modData = value.getCachedModuleData(module);
            Map<String, Tuple4<String, String, String, String>> _displayData =
                _updateDisplayData(modData);
            return Form(
                key: _formKey,
                child: SingleChildScrollView(
                    child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    ListView.builder(
                        padding: const EdgeInsets.all(5),
                        shrinkWrap: true,
                        itemCount: _displayData.length * 3,
                        itemBuilder: (BuildContext context, int index) {
                          int i = index ~/ 3;
                          switch (index % 3) {
                            case 0:
                              return Text(
                                  _displayData.values.elementAt(i).item1);
                            case 1:
                              switch (_displayData.values.elementAt(i).item3) {
                                case "date":
                                  return InputDatePickerFormField(
                                    initialDate: _displayData.values
                                                .elementAt(i)
                                                .item2 ==
                                            ""
                                        ? null
                                        : DateTime.parse(_displayData.values
                                            .elementAt(i)
                                            .item2),
                                    onDateSaved: (date) {
                                      _savedValues[_displayData.keys
                                          .elementAt(i)
                                          .toLowerCase()] = date.toString();
                                    },
                                    firstDate: DateTime.now().subtract(
                                        const Duration(days: 365 * 10)),
                                    lastDate: DateTime.now()
                                        .add(const Duration(days: 365 * 10)),
                                  );
                                case "external":
                                  return ExternalDropdown(
                                      idField: _displayData.keys.elementAt(i),
                                      extModule: _displayData.values
                                          .elementAt(i)
                                          .item4,
                                      curVal: _displayData.values
                                          .elementAt(i)
                                          .item2);
                                case "text":
                                default:
                                  return TextFormField(
                                    initialValue:
                                        _displayData.values.elementAt(i).item2,
                                    onSaved: (fieldVal) {
                                      _savedValues[_displayData.keys
                                          .elementAt(i)
                                          .toLowerCase()] = fieldVal ?? "";
                                    },
                                  );
                              }
                            case 2:
                            default:
                              return const Divider(
                                height: 20,
                                thickness: 2,
                                indent: 10,
                                endIndent: 10,
                              );
                          }
                        }),
                  ],
                )));
          },
        ),
        floatingActionButton:
            Consumer<NetworkState>(builder: (context, valueNet, child) {
          return Consumer<CachedState>(builder: (context, valueCache, child) {
            bool isOnline = valueNet.getLastStatus() != HttpUtils.timedOut;
            return FloatingActionButton(
              onPressed: () {
                //if (_formKey.currentState!.validate()) {
                _formKey.currentState!.save();
                var data = _savedValues;
                if (isOnline) {
                  HttpUtils.postEdit(data, module, id);
                } else {
                  valueCache.cacheModuleData(
                      "post+" + module + "+" + id.toString(), data.toString());
                }
                //}
                Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (context) => ModuleScreen(module: module)));
              },
              tooltip: 'Edit',
              child: const Icon(Icons.check),
            );
          });
        }));
  }
}
