# Changelog

All notable changes to `whatsapp` will be documented in this file

## Unreleased

- Add `photo64(string $file, string $filename)` method on `WhatsappFile::class`
- Support default APIs(`wppconnect-server`, `whatsapp-http-api`)
- Fix documentation, info about APIs sessions(`wppconnect-server`, `whatsapp-http-api`)
- Config file refactor(`config/whatsapp-notification-channel/services.php` -> `config/whatsapp-notification-channel.php`)
- `Whatsapp::class` refactor for On-Demand sending
- Make `Whatsapp::class` a singleton
- Add support for `X-Api-Key` api key and `Authorization` bearer token
- Fixes on exception handler, add support for Guzzle `ServerException::class`
- Change from `application/x-www-form-urlencoded` to `application/json`
- Support arrays on `file(array $file)` method (`WhatsappFile::class`)

## 1.0.3 - 2023-05-16

- Add laravel 10 support and custom `.env` on `WhatsappFile::class` construct
- Fix `whatsappSessionFieldName` on requests

## 1.0.2 - 2022-06-04

- Add laravel 9 support

## 1.0.1 - 2022-01-16

- Add session field name on `config` file

## 1.0.0 - 2022-01-09

- Initial Release.
