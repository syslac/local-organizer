import 'package:flutter/material.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:tuple/tuple.dart';
import 'package:provider/provider.dart';
import 'httpUtils.dart';
import 'networkState.dart';

void main() {
  runApp(ChangeNotifierProvider(
    create: (context) => NetworkState(),
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
          title: 'Local Organizer Demo',
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
  String _json = "";
  List<String> _mainListView = [];

  _MyHomePageState() {
    _reInitSharedPref();
  }

  @override
  void initState() {
    super.initState();
    _reInitSharedPref();
    HttpUtils.getSPJson().then((res) {
      setState(() {
        var parsed = HttpUtils.parseJson(res);
        _mainListView = parsed.item2;
      });
    });
  }

  void _reInitSharedPref() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      _json = (pref.getString('modules') ?? '');
    } catch (_) {
      SharedPreferences.setMockInitialValues({});
    }
  }

  void _refreshData() {
    if (Provider.of<NetworkState>(context, listen: false).getLastStatus() ==
        HttpUtils.timedOut) {
      return;
    }
    HttpUtils.queryApi('modules/fetch').then((res) async {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      prefs.setString('modules', res);
      setState(() {
        _json = (prefs.getString('modules') ?? '');
        var parsed = HttpUtils.parseJson(_json);
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
            ListView.builder(
                padding: const EdgeInsets.all(5),
                shrinkWrap: true,
                itemCount: _mainListView.length,
                itemBuilder: (BuildContext context, int index) {
                  return ListTile(
                      title: Text(_mainListView[index]),
                      onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (context) =>
                                  ModuleScreen(module: _mainListView[index]))));
                }),
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
  String _children = "";
  List<Tuple3<int, String, String>> _childrenList = [];

  ModuleScreenState(String mod) {
    _module = mod;
  }

  @override
  void initState() {
    super.initState();
    HttpUtils.queryApi(_module + '/fetch').then((res) async {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      prefs.setString(_module, res);
      setState(() {
        _children = (prefs.getString(_module) ?? '');
        var parsed = HttpUtils.parseJson(_children);
        List<String> idList = parsed.item1;
        List<String> itemsList = parsed.item2;
        List<String> peopleList = parsed.item3;
        for (int i = 0; i < itemsList.length; i++) {
          _childrenList.add(Tuple3<int, String, String>(
              int.parse(idList[i]), itemsList[i], peopleList[i]));
        }
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
        child: ListView.builder(
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
                              module: _childrenList[index].item1.toString()))));
            }),
      ),
    );
  }
}
