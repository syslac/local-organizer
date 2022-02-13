import 'package:flutter/material.dart';
import 'httpUtils.dart';
import 'package:provider/provider.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'package:tuple/tuple.dart';

class ExternalDropdown extends StatefulWidget {
  const ExternalDropdown(
      {Key? key, required this.extModule, required this.curVal})
      : super(key: key);

  final String extModule;
  final String curVal;

  @override
  State<ExternalDropdown> createState() =>
      _ExternalDropdownState(extModule, curVal);
}

class _ExternalDropdownState extends State<ExternalDropdown> {
  List<DropdownMenuItem<String>> _items = [];
  String _selected = "";
  String _extModule = "";

  _ExternalDropdownState(String mod, String curVal) {
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
    return DropdownButton(
      items: _items,
      value: _selected,
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

class EditScreen extends StatelessWidget {
  // In the constructor, require a Module.
  EditScreen({Key? key, required this.module, required this.id})
      : super(key: key);
  // Declare a field that holds the Module.
  final String module;
  final int id;
  final _formKey = GlobalKey<FormState>();

  Map<String, Tuple3<String, String, String>> _updateDisplayData(
      String allModuleData) {
    var parsed = HttpUtils.getSingleItemFromJson(allModuleData, id);
    Map<String, Tuple3<String, String, String>> retVal = {};
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
      retVal[convertedValue["header"]] = Tuple3<String, String, String>(
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
          Map<String, Tuple3<String, String, String>> _displayData =
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
                            return Text(_displayData.keys.elementAt(i));
                          case 1:
                            switch (_displayData.values.elementAt(i).item2) {
                              case "date":
                                return InputDatePickerFormField(
                                    initialDate: _displayData.values
                                                .elementAt(i)
                                                .item1 ==
                                            ""
                                        ? null
                                        : DateTime.parse(_displayData.values
                                            .elementAt(i)
                                            .item1),
                                    firstDate: DateTime.now().subtract(
                                        const Duration(days: 365 * 10)),
                                    lastDate: DateTime.now()
                                        .add(const Duration(days: 365 * 10)));
                              case "external":
                                return ExternalDropdown(
                                    extModule:
                                        _displayData.values.elementAt(i).item3,
                                    curVal:
                                        _displayData.values.elementAt(i).item1);
                              case "text":
                              default:
                                return TextFormField(
                                  initialValue:
                                      _displayData.values.elementAt(i).item1,
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
                  ElevatedButton(
                      onPressed: () {
                        if (_formKey.currentState!.validate()) {}
                      },
                      child: const Text("Submit")),
                ],
              )));
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(
                builder: (context) => EditScreen(module: module, id: id))),
        tooltip: 'Edit',
        child: const Icon(Icons.edit),
      ),
    );
  }
}
