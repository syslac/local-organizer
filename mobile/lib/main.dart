import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:local_organizer/httpUtils.dart';
import 'package:provider/provider.dart';
import 'networkState.dart';
import 'cachedState.dart';
import 'home.dart';

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
        return Consumer<CachedState>(
          builder: (context, cachedValue, child) {
            if (value.getLastStatus() != HttpUtils.timedOut) {
              cachedValue.processQueue((p0) {
                var data = jsonDecode(p0.item3) as Map;
                HttpUtils.postEdit(data, p0.item1, int.parse(p0.item2));
              });
            }
            return MaterialApp(
              title: 'Local Organizer',
              theme: ThemeData(
                primarySwatch: value.getCurrentColor(),
              ),
              home: const MyHomePage(title: 'Home'),
            );
          },
        );
      },
    );
  }
}
