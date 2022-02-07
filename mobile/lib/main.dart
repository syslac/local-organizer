import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:tuple/tuple.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Local Organizer Demo',
      theme: ThemeData(
        // This is the theme of your application.
        //
        // Try running your application with "flutter run". You'll see the
        // application has a blue toolbar. Then, without quitting the app, try
        // changing the primarySwatch below to Colors.green and then invoke
        // "hot reload" (press "r" in the console where you ran "flutter run",
        // or simply save your changes to "hot reload" in a Flutter IDE).
        // Notice that the counter didn't reset back to zero; the application
        // is not restarted.
        primarySwatch: Colors.blue,
      ),
      home: const MyHomePage(title: 'Home'),
    );
  }
}

class HttpUtils {
  static Future<String> _getSPJson() async {
    SharedPreferences pref = await SharedPreferences.getInstance();
    return (pref.getString('modules') ?? '');
  }

  static Future<int> _queryApiVersion() async {
    try {
      var url =
          Uri.parse('http://192.168.1.42/WordPress/local_organizer/version');
      var response = await http.get(url).timeout(
        const Duration(seconds: 1),
        onTimeout: () {
          // Time has run out, do what you wanted to do.
          return http.Response(
              'Error', 408); // Request Timeout response status code
        },
      );
      return response.statusCode;
    } catch (_) {
      return 408;
    }
  }

  static Future<String> _queryApi(String relativeApiPath) async {
    try {
      var url = Uri.parse(
          'http://192.168.1.42/WordPress/local_organizer/' + relativeApiPath);
      var response = await http.get(url).timeout(
        const Duration(seconds: 1),
        onTimeout: () {
          // Time has run out, do what you wanted to do.
          return http.Response('', 408); // Request Timeout response status code
        },
      );
      ;
      return response.body;
    } catch (_) {
      return '';
    }
  }

  static Tuple3<List<String>, List<String>, List<String>> _parseJson(
      String inJson) {
    Tuple3<List<String>, List<String>, List<String>> retList =
        Tuple3<List<String>, List<String>, List<String>>([], [], []);
    if (inJson == "") {
      return retList;
    }
    var data = jsonDecode(inJson) as Map;
    String idField = "";
    String textField = "";
    String extraField = "";
    if (data.containsKey("mobile")) {
      var mapMobile = data["mobile"] as Map;
      if (mapMobile.containsKey("id") &&
          mapMobile.containsKey("text") &&
          mapMobile.containsKey("extra")) {
        idField = mapMobile["id"] ?? "";
        textField = mapMobile["text"] ?? "";
        extraField = mapMobile["extra"] ?? "";
      }
    }
    for (var element in data["data"]) {
      var mapElement = element as Map;
      if (mapElement.containsKey(idField)) {
        retList.item1.add(element[idField]["data"].toString());
      } else {
        retList.item1.add('');
      }
      if (mapElement.containsKey(textField)) {
        retList.item2.add(element[textField]["data"].toString());
      } else {
        retList.item1.add('');
      }
      if (mapElement.containsKey(extraField)) {
        retList.item3.add(element[extraField]["data"].toString());
      } else {
        retList.item1.add('');
      }
    }
    return retList;
  }
}

class MyHomePage extends StatefulWidget {
  const MyHomePage({Key? key, required this.title}) : super(key: key);

  // This widget is the home page of your application. It is stateful, meaning
  // that it has a State object (defined below) that contains fields that affect
  // how it looks.

  // This class is the configuration for the state. It holds the values (in this
  // case the title) provided by the parent (in this case the App widget) and
  // used by the build method of the State. Fields in a Widget subclass are
  // always marked "final".

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
  int _lastHttpStatus = 0;
  String _connected = "";
  Color _netColor = Colors.blue;
  String _json = "";
  List<String> _mainListView = [];

  _MyHomePageState() {
    _reInitSharedPref();
    _checkOnline();
    const interval = Duration(seconds: 5);
    Timer.periodic(interval, (Timer t) => _checkOnline());
  }

  @override
  void initState() {
    super.initState();
    _reInitSharedPref();
    HttpUtils._getSPJson().then((res) {
      setState(() {
        var parsed = HttpUtils._parseJson(res);
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

  void _checkOnline() {
    HttpUtils._queryApiVersion().then((res) {
      setState(() {
        // This call to setState tells the Flutter framework that something has
        // changed in this State, which causes it to rerun the build method below
        _lastHttpStatus = res;
        if (res != 408) {
          _connected = "Online mode";
          _netColor = Colors.blue;
        } else {
          _connected = "Offline mode";
          _netColor = Colors.red;
        }
      });
    });
  }

  void _refreshData() {
    if (_lastHttpStatus == 404) {
      return;
    }
    HttpUtils._queryApi('modules/fetch').then((res) async {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      prefs.setString('modules', res);
      setState(() {
        _json = (prefs.getString('modules') ?? '');
        var parsed = HttpUtils._parseJson(_json);
        _mainListView = parsed.item2;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    // This method is rerun every time setState is called, for instance as done
    // by the _incrementCounter method above.
    //
    // The Flutter framework has been optimized to make rerunning build methods
    // fast, so that you can just rebuild anything that needs updating rather
    // than having to individually change instances of widgets.
    return Scaffold(
      appBar: AppBar(
        // Here we take the value from the MyHomePage object that was created by
        // the App.build method, and use it to set our appbar title.
        title: Text(widget.title),
        actions: <Widget>[Text(_connected)],
        backgroundColor: _netColor,
      ),
      body: Center(
        // Center is a layout widget. It takes a single child and positions it
        // in the middle of the parent.
        child: Column(
          // Column is also a layout widget. It takes a list of children and
          // arranges them vertically. By default, it sizes itself to fit its
          // children horizontally, and tries to be as tall as its parent.
          //
          // Invoke "debug painting" (press "p" in the console, choose the
          // "Toggle Debug Paint" action from the Flutter Inspector in Android
          // Studio, or the "Toggle Debug Paint" command in Visual Studio Code)
          // to see the wireframe for each widget.
          //
          // Column has various properties to control how it sizes itself and
          // how it positions its children. Here we use mainAxisAlignment to
          // center the children vertically; the main axis here is the vertical
          // axis because Columns are vertical (the cross axis would be
          // horizontal).
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
      ), // This trailing comma makes auto-formatting nicer for build methods.
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
    HttpUtils._queryApi(_module + '/fetch').then((res) async {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      prefs.setString(_module, res);
      setState(() {
        _children = (prefs.getString(_module) ?? '');
        var parsed = HttpUtils._parseJson(_children);
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
      appBar: AppBar(
        title: Text(_module),
      ),
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
