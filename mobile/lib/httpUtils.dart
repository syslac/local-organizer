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

  static Map<int, String> parseExtJson(String inJson) {
    Map<int, String> retMap = {};
    if (inJson == "") {
      return retMap;
    }
    Map<dynamic, dynamic> untypedMap = jsonDecode(inJson) as Map;
    untypedMap.forEach((key, value) {
      retMap[int.tryParse(key) ?? 0] = value.toString();
    });
    return retMap;
  }

  static bool dataJsonHasId(String inJson, int id) {
    if (inJson == "") {
      return false;
    }
    var data = jsonDecode(inJson) as Map;
    for (var element in data["data"]) {
      var mapElement = element as Map;
      if (!mapElement.containsKey("id")) {
        continue;
      }
      var mapFieldData = mapElement["id"] as Map;
      if (mapFieldData.containsKey("data") && mapFieldData["data"] == id) {
        return true;
      }
    }
    return false;
  }

  static String editJsonId(String inJson, int id, Map<String, String> newData) {
    if (inJson == "") {
      return "";
    }
    var origData = jsonDecode(inJson);
    var data = origData as Map;
    int i = 0;
    for (var element in data["data"]) {
      var mapElement = element as Map;
      if (!mapElement.containsKey("id")) {
        continue;
      }
      var mapFieldData = mapElement["id"] as Map;
      if (mapFieldData.containsKey("data") && mapFieldData["data"] == id) {
        newData.forEach((key, value) {
          if (mapElement.containsKey(key) &&
              (mapElement[key] as Map).containsKey("data") &&
              (mapElement[key] as Map).containsKey("edit_data")) {
            origData["data"][i][key]["data"] = value;
            origData["data"][i][key]["edit_data"] = value;
          }
        });
      }
      i++;
    }
    return jsonEncode(origData);
  }

  static String addToJson(String inJson, Map<String, String> newData) {
    if (inJson == "") {
      return "";
    }
    var origData = jsonDecode(inJson);
    var data = origData as Map;
    Map<dynamic, dynamic> lastElement = {};
    int lastId = 0;
    for (var element in data["data"]) {
      var mapElement = element as Map;
      if (!mapElement.containsKey("id")) {
        continue;
      }
      var mapFieldData = mapElement["id"] as Map;
      if (mapFieldData.containsKey("data") && mapFieldData["data"] >= lastId) {
        lastId = mapFieldData["data"];
        lastElement = jsonDecode(jsonEncode(mapElement));
      }
    }
    if (lastElement.isNotEmpty) {
      newData.forEach((key, value) {
        if (lastElement.containsKey(key) &&
            (lastElement[key] as Map).containsKey("data") &&
            (lastElement[key] as Map).containsKey("edit_data")) {
          lastElement[key]["data"] = value;
          lastElement[key]["edit_data"] = value;
        }
      });
      lastElement["id"]["data"] = lastId + 1;
      origData["data"].add(lastElement);
      return jsonEncode(origData);
    }
    return "";
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

  static Map<dynamic, dynamic> getSingleItemFromJson(String inJson, int id) {
    if (inJson == "" || id <= 0) {
      return {};
    }
    Map<dynamic, dynamic> data = jsonDecode(inJson) as Map;
    if (!data.containsKey("data")) {
      return {};
    }
    List<dynamic> elementList = data["data"] as List;
    Map<dynamic, dynamic> foundItem = {};
    for (var element in elementList) {
      Map<dynamic, dynamic> mapEl = element as Map;
      if (mapEl.containsKey("id") && (mapEl["id"] as Map).containsKey("data")) {
        if (mapEl["id"]["data"] == id) {
          foundItem = mapEl;
        }
      }
    }
    return foundItem;
  }

  static Map<dynamic, dynamic> buildEmptyItemFromJson(String inJson) {
    if (inJson == "") {
      return {};
    }
    Map<dynamic, dynamic> data = jsonDecode(inJson) as Map;
    if (!data.containsKey("data")) {
      return {};
    }
    List<dynamic> elementList = data["data"] as List;
    Map<dynamic, dynamic> foundItem = {};
    if (elementList.isNotEmpty) {
      var element = elementList[0];
      Map<dynamic, dynamic> mapEl = element as Map;
      mapEl.forEach((key, value) {
        Map<dynamic, dynamic> singleField = value as Map;
        singleField["data"] = "";
        singleField["edit_data"] = "";
        foundItem[key] = singleField;
      });
    }
    return foundItem;
  }

  static Future<bool> postEdit(
      Map<dynamic, dynamic> data, String module, int id) async {
    Uri url;
    if (id == 0) {
      url = Uri.parse(apiRoot + module + '/add');
    } else {
      url = Uri.parse(apiRoot + module + '/edit/id/' + id.toString());
    }

    var response = await http.post(url, body: data);

    if (response.statusCode == 302) {
      return true;
    } else {
      return false;
    }
  }
}
