import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:async';
import 'dart:convert';
import 'package:tuple/tuple.dart';

class HttpUtils {
  static const int timedOut = 408;
  static const String apiRoot =
      "http://192.168.1.42/WordPress/local_organizer/";

  static Future<String> getSPJson() async {
    SharedPreferences pref = await SharedPreferences.getInstance();
    return (pref.getString('modules') ?? '');
  }

  static Future<int> queryApiVersion() async {
    try {
      var url = Uri.parse(apiRoot + 'version');
      var response = await http.get(url).timeout(
        const Duration(seconds: 1),
        onTimeout: () {
          // Time has run out, do what you wanted to do.
          return http.Response(
              'Error', timedOut); // Request Timeout response status code
        },
      );
      return response.statusCode;
    } catch (_) {
      return timedOut;
    }
  }

  static Future<String> queryApi(String relativeApiPath) async {
    try {
      var url = Uri.parse(apiRoot + relativeApiPath);
      var response = await http.get(url).timeout(
        const Duration(seconds: 1),
        onTimeout: () {
          // Time has run out, do what you wanted to do.
          return http.Response(
              '', timedOut); // Request Timeout response status code
        },
      );
      ;
      return response.body;
    } catch (_) {
      return '';
    }
  }

  static Tuple3<List<String>, List<String>, List<String>> parseJson(
      String inJson) {
    var retList = Tuple3<List<String>, List<String>, List<String>>([], [], []);
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
