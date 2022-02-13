import 'package:flutter/material.dart';
import 'httpUtils.dart';
import 'package:provider/provider.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'editView.dart';

class DetailScreen extends StatelessWidget {
  // In the constructor, require a Module.
  DetailScreen({Key? key, required this.module, required this.id})
      : super(key: key);
  // Declare a field that holds the Module.
  final String module;
  final int id;

  Map<String, String> _updateDisplayData(String allModuleData) {
    var parsed = HttpUtils.getSingleItemFromJson(allModuleData, id);
    Map<String, String> retVal = {};
    parsed.forEach((key, value) {
      Map<dynamic, dynamic> convertedValue = value as Map;
      if (!convertedValue.containsKey("header") ||
          !convertedValue.containsKey("data")) {
        return;
      }
      if (convertedValue.containsKey("hide") && convertedValue["hide"]) {
        return;
      }
      retVal[convertedValue["header"]] = convertedValue["data"] == null
          ? ""
          : convertedValue["data"].toString();
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
      body: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Consumer<CachedState>(
            builder: (context, value, child) {
              var modData = value.getCachedModuleData(module);
              Map<String, String> _displayData = _updateDisplayData(modData);
              return GridView.count(
                crossAxisCount: 2,
                mainAxisSpacing: 0,
                shrinkWrap: true,
                childAspectRatio: 3,
                padding: EdgeInsets.zero,
                children: List.generate(_displayData.length * 2, (index) {
                  switch (index % 2) {
                    case 0:
                      return Text(_displayData.keys.elementAt(index ~/ 2),
                          style: Theme.of(context).textTheme.headline5);
                    case 1:
                      return Text(_displayData.values.elementAt(index ~/ 2));
                    default:
                      return Text("");
                  }
                }),
              );
            },
          )),
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
