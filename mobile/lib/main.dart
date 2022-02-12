import 'package:flutter/material.dart';
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
