import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/app_config.dart';
import 'app_snackbar.dart';

class ApiService {
  static const _storage = FlutterSecureStorage();
  static const String _tokenKey = 'auth_token';
  static const _sensitiveKeys = {
    'authorization',
    'password',
    'password_confirmation',
    'current_password',
    'pin',
    'token',
  };

  // ── Token management ────────────────────────────────────────────────
  static Future<void> saveToken(String token) async =>
      _storage.write(key: _tokenKey, value: token);

  static Future<String?> getToken() async =>
      _storage.read(key: _tokenKey);

  static Future<void> deleteToken() async =>
      _storage.delete(key: _tokenKey);

  // ── HTTP helpers ────────────────────────────────────────────────────
  static Future<Map<String, String>> _headers({bool auth = true}) async {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  static Uri _uri(String path, [Map<String, dynamic>? params]) {
    final uri = Uri.parse('${AppConfig.baseUrl}$path');
    if (params != null && params.isNotEmpty) {
      return uri.replace(
        queryParameters: params.map((k, v) => MapEntry(k, v.toString())),
      );
    }
    return uri;
  }

  static void _log(String message) {
    if (!kDebugMode) return;
    final pattern = RegExp('.{1,800}');
    for (final match in pattern.allMatches(message)) {
      debugPrint(match.group(0));
    }
  }

  static Map<String, dynamic> _sanitizeMap(Map<String, dynamic> input) {
    return input.map((key, value) {
      final lower = key.toLowerCase();
      if (_sensitiveKeys.contains(lower)) {
        return MapEntry(key, '***');
      }
      return MapEntry(key, value);
    });
  }

  static Map<String, String> _sanitizeHeaders(Map<String, String> headers) {
    return headers.map((key, value) {
      if (_sensitiveKeys.contains(key.toLowerCase())) {
        return MapEntry(key, '***');
      }
      return MapEntry(key, value);
    });
  }

  static void _logRequest(
    String method,
    Uri uri, {
    Map<String, String>? headers,
    Map<String, dynamic>? body,
    Map<String, String>? fields,
    Map<String, File>? files,
  }) {
    _log('➡️ API REQUEST [$method] $uri');
    if (headers != null) {
      _log('Headers: ${jsonEncode(_sanitizeHeaders(headers))}');
    }
    if (body != null && body.isNotEmpty) {
      _log('Body: ${jsonEncode(_sanitizeMap(body))}');
    }
    if (fields != null && fields.isNotEmpty) {
      _log('Fields: ${jsonEncode(_sanitizeMap(fields))}');
    }
    if (files != null && files.isNotEmpty) {
      final fileInfo = files.map((key, file) => MapEntry(key, file.path.split(Platform.pathSeparator).last));
      _log('Files: ${jsonEncode(fileInfo)}');
    }
  }

  static void _logResponse(
    String method,
    Uri uri,
    http.Response response,
    Stopwatch watch,
  ) {
    _log('⬅️ API RESPONSE [$method] ${response.statusCode} $uri (${watch.elapsedMilliseconds} ms)');
    _log('Response body: ${response.body}');
  }

  static void _logFailure(String method, Uri uri, Object error, Stopwatch watch) {
    _log('❌ API ERROR [$method] $uri (${watch.elapsedMilliseconds} ms)');
    _log('Error: $error');
  }

  // ── GET ─────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? params,
    bool auth = true,
  }) async {
    final uri = _uri(path, params);
    final watch = Stopwatch()..start();
    try {
      final headers = await _headers(auth: auth);
      _logRequest('GET', uri, headers: headers);
      final response = await http
          .get(uri, headers: headers)
          .timeout(const Duration(seconds: 30));
      _logResponse('GET', uri, response, watch);
      return _parse(response);
    } on SocketException catch (e) {
      _logFailure('GET', uri, e, watch);
      throw ApiException('No internet connection.');
    } catch (e) {
      _logFailure('GET', uri, e, watch);
      throw ApiException(e.toString());
    } finally {
      watch.stop();
    }
  }

  // ── POST ────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool auth = true,
  }) async {
    final uri = _uri(path);
    final watch = Stopwatch()..start();
    try {
      final headers = await _headers(auth: auth);
      final payload = body ?? <String, dynamic>{};
      _logRequest('POST', uri, headers: headers, body: payload);
      final response = await http
          .post(
            uri,
            headers: headers,
            body: jsonEncode(payload),
          )
          .timeout(const Duration(seconds: 30));
      _logResponse('POST', uri, response, watch);
      return _parse(response);
    } on SocketException catch (e) {
      _logFailure('POST', uri, e, watch);
      throw ApiException('No internet connection.');
    } catch (e) {
      _logFailure('POST', uri, e, watch);
      throw ApiException(e.toString());
    } finally {
      watch.stop();
    }
  }

  // ── Multipart POST (file upload) ────────────────────────────────────
  static Future<Map<String, dynamic>> postMultipart(
    String path, {
    required Map<String, String> fields,
    Map<String, File>? files,
  }) async {
    final uri = _uri(path);
    final watch = Stopwatch()..start();
    try {
      final token = await getToken();
      final request = http.MultipartRequest('POST', uri);
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';
      request.fields.addAll(fields);

      _logRequest(
        'MULTIPART POST',
        uri,
        headers: request.headers,
        fields: fields,
        files: files,
      );

      if (files != null) {
        for (final entry in files.entries) {
          request.files.add(
            await http.MultipartFile.fromPath(entry.key, entry.value.path),
          );
        }
      }

      final streamed = await request.send().timeout(const Duration(seconds: 60));
      final response = await http.Response.fromStream(streamed);
      _logResponse('MULTIPART POST', uri, response, watch);
      return _parse(response);
    } on SocketException catch (e) {
      _logFailure('MULTIPART POST', uri, e, watch);
      throw ApiException('No internet connection.');
    } catch (e) {
      _logFailure('MULTIPART POST', uri, e, watch);
      throw ApiException(e.toString());
    } finally {
      watch.stop();
    }
  }

  // ── Response parser ──────────────────────────────────────────────────
  static Map<String, dynamic> _parse(http.Response response) {
    try {
      final data = jsonDecode(response.body) as Map<String, dynamic>;
      final message = (data['message'] ?? 'An error occurred.').toString();
      if (response.statusCode == 401) {
        throw UnauthorizedException(message.isEmpty ? 'Unauthenticated.' : message);
      }
      if (response.statusCode >= 400) {
        if (response.statusCode == 422) {
          AppSnackbar.showError(message);
        }
        throw ApiException(message);
      }
      return data;
    } catch (e) {
      if (e is ApiException) rethrow;
      _log('API parse error: $e');
      _log('Raw body: ${response.body}');
      throw ApiException('Invalid server response (${response.statusCode}).');
    }
  }
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  @override
  String toString() => message;
}

class UnauthorizedException extends ApiException {
  UnauthorizedException(super.message);
}

