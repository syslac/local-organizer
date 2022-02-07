import 'dart:async';
import 'package:flutter/material.dart';
import "httpUtils.dart";

class NetworkState extends ChangeNotifier {
  int _lastHttpStatus = 0;

  NetworkState() {
    _checkOnline();
    const interval = Duration(seconds: 5);
    Timer.periodic(interval, (Timer t) => _checkOnline());
  }

  void _checkOnline() {
    HttpUtils.queryApiVersion().then((res) {
      _lastHttpStatus = res;
      notifyListeners();
    });
  }

  int getLastStatus() {
    return _lastHttpStatus;
  }

  MaterialColor getCurrentColor() {
    if (_lastHttpStatus == HttpUtils.timedOut) {
      return Colors.red;
    } else {
      return Colors.blue;
    }
  }

  String getConnectionString() {
    if (_lastHttpStatus == HttpUtils.timedOut) {
      return "Offline mode";
    } else {
      return "Online mode";
    }
  }
}
