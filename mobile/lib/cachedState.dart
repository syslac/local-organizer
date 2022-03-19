import 'dart:async';
import 'dart:collection';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'httpUtils.dart';
import 'package:tuple/tuple.dart';

class CachedState extends ChangeNotifier {
  final Map<String, String> _cachedModulesJson = {};
  final Queue<Tuple3<String, String, String>> _offlineUpdatesQueue =
      Queue<Tuple3<String, String, String>>();

  CachedState() {
    _reInitSharedPref();
    _initDataFromSharedPref();
  }

  void _reInitSharedPref() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      _cachedModulesJson["modules"] = (pref.getString('get+modules') ?? '');
      notifyListeners();
    } catch (_) {
      SharedPreferences.setMockInitialValues({});
    }
  }

  void _initDataFromSharedPref() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.getKeys().forEach((element) {
        List<String> parts = element.split("+");
        if (parts.length == 2 && parts[0] == "get") {
          _cachedModulesJson[parts[1]] = pref.getString(element) ?? '';
        } else if (parts.length == 3 && parts[0] == "post") {
          _offlineUpdatesQueue.add(Tuple3<String, String, String>(
              parts[1], parts[2], pref.getString(element) ?? ''));
        }
      });
      notifyListeners();
    } catch (_) {}
  }

  String getCachedModuleData(String module) {
    return _cachedModulesJson[module] ?? "";
  }

  Tuple3<List<String>, List<String>, List<String>> getDecodedCachedModuleData(
      String module) {
    return HttpUtils.parseJson(_cachedModulesJson[module] ?? "");
  }

  void cacheModuleData(String module, String data,
      [bool overwrite = true]) async {
    if (!_cachedModulesJson.containsKey(module) || overwrite) {
      _cachedModulesJson[module] = data;
      notifyListeners();
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.setString("get+" + module, data);
    }
  }

  void cacheEditData(String module, String data,
      [bool overwrite = true]) async {
    if (!_cachedModulesJson.containsKey(module) || overwrite) {
      List<String> parts = module.split("+");
      _offlineUpdatesQueue
          .add(Tuple3<String, String, String>(parts[0], parts[1], data));
      notifyListeners();
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.setString("post+" + module, data);
    }
  }

  Tuple3<String, String, String> returnFromQueue() {
    return _offlineUpdatesQueue.removeFirst();
  }

  void processQueue(
      void Function(Tuple3<String, String, String>) action) async {
    while (_offlineUpdatesQueue.isNotEmpty) {
      Tuple3<String, String, String> curElement = returnFromQueue();
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.remove("post+" + curElement.item1 + "+" + curElement.item2);
      action(curElement);
    }
  }
}
