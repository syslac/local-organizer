import 'package:flutter/material.dart';
import 'httpUtils.dart';
import 'package:provider/provider.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'moduleView.dart';

class MyHomePage extends StatefulWidget {
  const MyHomePage({Key? key, required this.title}) : super(key: key);

  final String title;

  @override
  State<MyHomePage> createState() => _MyHomePageState();
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
