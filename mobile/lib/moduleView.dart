import 'package:flutter/material.dart';
import 'httpUtils.dart';
import 'package:provider/provider.dart';
import 'package:tuple/tuple.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'detailView.dart';
import 'editView.dart';

class ModuleScreen extends StatelessWidget {
  // In the constructor, require a Module.
  ModuleScreen({Key? key, required this.module}) : super(key: key);
  // Declare a field that holds the Module.
  final String module;

  //@override
  //State<ModuleScreen> createState() => ModuleScreenState(this.module);

  List<Tuple3<int, String, String>> _decodedJsonToLocalState(
      Tuple3<List<String>, List<String>, List<String>> inJson) {
    List<Tuple3<int, String, String>> toRet = [];
    List<String> idList = inJson.item1;
    List<String> itemsList = inJson.item2;
    List<String> peopleList = inJson.item3;
    for (int i = 0; i < itemsList.length; i++) {
      toRet.add(Tuple3<int, String, String>(
          int.parse(idList[i]), itemsList[i], peopleList[i]));
    }
    return toRet;
  }

  @override
  Widget build(BuildContext context) {
    List<Tuple3<int, String, String>> _childrenList = [];
    if (Provider.of<NetworkState>(context, listen: false).getLastStatus() !=
        HttpUtils.timedOut) {
      HttpUtils.queryApi(module + '/fetch').then((res) {
        Provider.of<CachedState>(context, listen: false)
            .cacheModuleData(module, res);
        var parsed = HttpUtils.parseJson(res);
        _childrenList = _decodedJsonToLocalState(parsed);
      });
    }
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
              _childrenList = _decodedJsonToLocalState(
                  value.getDecodedCachedModuleData(module));
              return ListView.builder(
                  padding: const EdgeInsets.all(5),
                  shrinkWrap: true,
                  itemCount: _childrenList.length,
                  itemBuilder: (BuildContext context, int index) {
                    return ListTile(
                        title: Text(_childrenList[index].item2 +
                            (_childrenList[index].item3 == "null"
                                ? ""
                                : " | for " + _childrenList[index].item3)),
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (context) => DetailScreen(
                                    module: module,
                                    id: _childrenList[index].item1))));
                  });
            },
          )),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(
                builder: (context) => EditScreen(module: module, id: 0))),
        tooltip: 'New',
        child: const Icon(Icons.add),
      ),
    );
  }
}
