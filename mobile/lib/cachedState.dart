import 'dart:async';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'httpUtils.dart';
import 'package:tuple/tuple.dart';

class CachedState extends ChangeNotifier {
  final Map<String, String> _cachedModulesJson = {};
  final Map<String, List<String>> _offlineUpdatesQueue = {};

  CachedState() {
    _reInitSharedPref();
    _initDataFromSharedPref();
  }

  void _reInitSharedPref() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      _cachedModulesJson["modules"] = (pref.getString('get_modules') ?? '');
      notifyListeners();
    } catch (_) {
      SharedPreferences.setMockInitialValues({});
    }
  }

  void _initDataFromSharedPref() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.getKeys().forEach((element) {
        List<String> parts = element.split("_");
        if (parts.length == 2 && parts[0] == "get") {
          _cachedModulesJson[parts[1]] = pref.getString(element) ?? '';
        } else if (parts.length == 3 && parts[0] == "post") {
          _offlineUpdatesQueue[parts[1]]?.add(pref.getString(element) ?? '');
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
      pref.setString("get_" + module, data);
    }
  }
}
