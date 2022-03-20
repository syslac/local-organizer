import 'dart:async';
import 'package:flutter/material.dart';
import "httpUtils.dart";

class NetworkState extends ChangeNotifier {
  int _lastHttpStatus = 0;

  NetworkState() {
    _checkOnline();
    const interval = Duration(seconds: 30);
    Timer.periodic(interval, (Timer t) => _checkOnline());
  }

  void _checkOnline() {
    HttpUtils.queryApiVersion().then((res) {
      bool needNotify = false;
      if (_lastHttpStatus != res) {
        needNotify = true;
      }
      _lastHttpStatus = res;
      if (needNotify) {
        notifyListeners();
      }
    });
  }

  int getLastStatus() {
    return _lastHttpStatus;
  }

  MaterialColor getCurrentColor() {
    if (_lastHttpStatus == HttpUtils.timedOut) {
      return Colors.grey;
    } else {
      return Colors.green;
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
