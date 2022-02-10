import 'package:flutter/material.dart';
import 'dart:async';
import 'package:tuple/tuple.dart';
import 'package:provider/provider.dart';
import 'httpUtils.dart';
import 'networkState.dart';
import 'cachedState.dart';

void main() {
  runApp(MultiProvider(
    providers: [
      ChangeNotifierProvider(create: (context) => NetworkState()),
      ChangeNotifierProvider(create: (context) => CachedState()),
    ],
    child: const MyApp(),
  ));
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return Consumer<NetworkState>(
      builder: (context, value, child) {
        return MaterialApp(
          title: 'Local Organizer',
          theme: ThemeData(
            primarySwatch: value.getCurrentColor(),
          ),
          home: const MyHomePage(title: 'Home'),
        );
      },
    );
  }
}

class MyHomePage extends StatefulWidget {
  const MyHomePage({Key? key, required this.title}) : super(key: key);

  final String title;

  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class ModuleScreen extends StatefulWidget {
  // In the constructor, require a Module.
  ModuleScreen({Key? key, required this.module}) : super(key: key);
  // Declare a field that holds the Module.
  final String module;

  @override
  State<ModuleScreen> createState() => ModuleScreenState(this.module);
}

class _MyHomePageState extends State<MyHomePage> {
  List<String> _mainListView = [];

  _MyHomePageState();

  @override
  void initState() {
    super.initState();
    HttpUtils.getSPJson().then((res) {
      setState(() {
        var parsed = HttpUtils.parseJson(res);
        _mainListView = parsed.item2;
      });
    });
  }

  void _refreshData() {
    if (Provider.of<NetworkState>(context, listen: false).getLastStatus() ==
        HttpUtils.timedOut) {
      return;
    }
    HttpUtils.queryApi('modules/fetch').then((res) {
      Provider.of<CachedState>(context, listen: false)
          .cacheModuleData("modules", res);
      setState(() {
        var parsed = HttpUtils.parseJson(res);
        _mainListView = parsed.item2;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title), actions: <Widget>[
        Consumer<NetworkState>(
          builder: (context, value, child) {
            return Text(value.getConnectionString());
          },
        )
      ]),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Text(
              'Modules',
            ),
            Consumer<CachedState>(
              builder: (context, value, child) {
                _mainListView =
                    value.getDecodedCachedModuleData("modules").item2;
                return ListView.builder(
                    padding: const EdgeInsets.all(5),
                    shrinkWrap: true,
                    itemCount: _mainListView.length,
                    itemBuilder: (BuildContext context, int index) {
                      return ListTile(
                          title: Text(_mainListView[index]),
                          onTap: () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                  builder: (context) => ModuleScreen(
                                      module: _mainListView[index]))));
                    });
              },
            )
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _refreshData,
        tooltip: 'Refresh data',
        child: const Icon(Icons.refresh),
      ),
    );
  }
}

class ModuleScreenState extends State<ModuleScreen> {
  String _module = "";
  List<Tuple3<int, String, String>> _childrenList = [];

  ModuleScreenState(String mod) {
    _module = mod;
  }

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
  void initState() {
    super.initState();
    if (Provider.of<NetworkState>(context, listen: false).getLastStatus() ==
        HttpUtils.timedOut) {
      return;
    }
    HttpUtils.queryApi(_module + '/fetch').then((res) {
      Provider.of<CachedState>(context, listen: false)
          .cacheModuleData(_module, res);
      setState(() {
        var parsed = HttpUtils.parseJson(res);
        _childrenList = _decodedJsonToLocalState(parsed);
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_module), actions: <Widget>[
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
                  value.getDecodedCachedModuleData(_module));
              return ListView.builder(
                  padding: const EdgeInsets.all(5),
                  shrinkWrap: true,
                  itemCount: _childrenList.length,
                  itemBuilder: (BuildContext context, int index) {
                    return ListTile(
                        title: Text(_childrenList[index].item2 +
                            " | for " +
                            _childrenList[index].item3),
                        onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (context) => ModuleScreen(
                                    module: _childrenList[index]
                                        .item1
                                        .toString()))));
                  });
            },
          )),
    );
  }
}
