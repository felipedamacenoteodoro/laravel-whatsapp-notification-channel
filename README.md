# Whatsapp Notifications Channel for Laravel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-packagist]

This package makes it easy to send Whatsapp notification using [Venom API](https://github.com/orkestral/venom) with Laravel.

This package was created based on the telegram notification package.

Thanks to Irfaq Syed for the codebase used here. 

The packages is 100% free and opensource, if you are interested in hiring paid support, installation or implementation, [Felipe D. Teodoro](https://api.whatsapp.com/send?phone=5521972745771&text=Gostaria%20de%20mais%20informa%C3%A7%C3%B5es%20sobre%20o%20suporte%20do%20pacote%20Whatsapp%20Notifications%20Channel%20for%20Laravel) 

## Contents

- [Installation](#installation)
  - [Setting up your Whatsapp session](#setting-up-your-whatsapp-bot)
  - [Retrieving SESSION ID](#retrieving-chat-id)
  - [Using in Lumen](#using-in-lumen)
  - [Proxy or Bridge Support](#proxy-or-bridge-support)
- [Usage](#usage)
  - [Text Notification](#text-notification)
  - [Attach a Contact](#attach-a-contact)
  - [Attach an Audio](#attach-an-audio)
  - [Attach a Photo](#attach-a-photo)
  - [Attach a Document](#attach-a-document)
  - [Attach a Location](#attach-a-location)
  - [Attach a Video](#attach-a-video)
  - [Attach a GIF File](#attach-a-gif-file)
  - [Routing a Message](#routing-a-message)
  - [Handling Response](#handling-response)
  - [On-Demand Notifications](#on-demand-notifications)
  - [Sending to Multiple Recipients](#sending-to-multiple-recipients)
- [Available Methods](#available-methods)
  - [Shared Methods](#shared-methods)
  - [Whatsapp Message methods](#whatsapp-message-methods)
  - [Whatsapp Location methods](#whatsapp-location-methods)
  - [Whatsapp File methods](#whatsapp-file-methods)
  - [Whatsapp Contact methods](#whatsapp-contact-methods)
  - [Whatsapp Poll methods](#whatsapp-poll-methods)
- [Alternatives](#alternatives)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require felipedamacenoteodoro/laravel-whatsapp-notification-channel
```

## Publish config file

Publish the config file:

```bash
php artisan vendor:publish --tag=whatsapp-notification-channel-config
```

## Setting up your Whatsapp session

Set your venom session [Venom Session](https://orkestral.github.io/venom/pages/Getting%20Started/creating-client.html) and configure your Whatsapp Session:

```php
# config/whatsapp-notification-channel/services.php

'whatsapp-bot-api' => [
    'whatsappSession' => env('WHATSAPP_API_SESSION', 'YOUR API WHATSAPP SESSION HERE')
],
```

## Proxy or Bridge Support

You may not be able to send notifications if Whatsapp API is not accessible in your country,
you can either set a proxy by following the instructions [here](http://docs.guzzlephp.org/en/stable/quickstart.html#environment-variables) or
use a web bridge by setting the `base_uri` config above with the bridge uri.

You can set `HTTPS_PROXY` in your `.env` file.

## Usage

You can now use the channel in your `via()` method inside the Notification class.

### Text Notification

```php
use NotificationChannels\Whatsapp\WhatsappMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return ["whatsapp"];
    }

    public function toWhatsapp($notifiable)
    {
        $url = url('/invoice/' . $this->invoice->id);

        return WhatsappMessage::create()
            // Optional recipient user id.
            ->to($notifiable->whatsapp_number)
            // Markdown supported.
            ->content("Hello there!\nYour invoice has been *PAID*")

            // (Optional) Blade template for the content.
            // ->view('notification', ['url' => $url])
    }
}
```

### Attach an Audio

```php
public function toWhatsapp($notifiable)
{
    return WhatsappFile::create()
            ->to($notifiable->whatsapp_number) // Optional
            ->content('Audio') // Optional Caption
            ->audio('/path/to/audio.mp3');
}
```

### Attach a Photo

```php
public function toWhatsapp($notifiable)
{
    return WhatsappFile::create()
        ->to($notifiable->whatsapp_number) // Optional
        ->content('Awesome *bold* text')
        ->file('/storage/archive/6029014.jpg', 'photo'); // local photo

        // OR using a helper method with or without a remote file.
        // ->photo('https://file-examples-com.github.io/uploads/2017/10/file_example_JPG_1MB.jpg');
}
```

### Attach a Document

```php
public function toWhatsapp($notifiable)
{
    return WhatsappFile::create()
        ->to($notifiable->whatsapp_number) // Optional
        ->content('Did you know we can set a custom filename too?')
        ->document('https://file-examples-com.github.io/uploads/2017/10/file-sample_150kB.pdf', 'sample.pdf');
}
```

### Attach a Location

```php
public function toWhatsapp($notifiable)
{
    return WhatsappLocation::create()
        ->latitude('40.6892494')
        ->longitude('-74.0466891');
}
```

### Attach a Video

```php
public function toWhatsapp($notifiable)
{
    return WhatsappFile::create()
        ->content('Sample *video* notification!')
        ->video('https://file-examples-com.github.io/uploads/2017/04/file_example_MP4_480_1_5MG.mp4');
}
```

### Attach a GIF File

```php
public function toWhatsapp($notifiable)
{
    return WhatsappFile::create()
        ->content('Woot! We can send animated gif notifications too!')
        ->animation('https://sample-videos.com/gif/2.gif');

        // Or local file
        // ->animation('/path/to/some/animated.gif');
}
```
### Routing a Message

You can either send the notification by providing with the whatapp number of the recipient to the `to($whatsapp_number)` method like shown in the previous examples or add a `routeNotificationForWhatsapp()` method in your notifiable model:

```php
/**
 * Route notifications for the Whatsapp channel.
 *
 * @return int
 */
public function routeNotificationForWhatsapp()
{
    return $this->whatsapp_number;
}
```

### Handling Response

You can make use of the [notification events](https://laravel.com/docs/5.8/notifications#notification-events) to handle the response from Whatsapp. On success, your event listener will receive a [Message](https://core.whatsapp.org/bots/api#message) object with various fields as appropriate to the notification type.

For a complete list of response fields, please refer the Venom Whatsapp API's [Message object](https://orkestral.github.io/venom/index.html) docs.

### On-Demand Notifications

> Sometimes you may need to send a notification to someone who is not stored as a "user" of your application. Using the `Notification::route` method, you may specify ad-hoc notification routing information before sending the notification. For more details, you can check out the [on-demand notifications][link-on-demand-notifications] docs.

```php
use Illuminate\Support\Facades\Notification;

Notification::route('whatsapp', 'WHATSAPP_SESSION')
            ->notify(new InvoicePaid($invoice));
```

### Sending to Multiple Recipients

Using the [notification facade][link-notification-facade] you can send a notification to multiple recipients at once.

> If you're sending bulk notifications to multiple users, the Whatsapp API will not allow more than 30 messages per second or so. 
> Consider spreading out notifications over large intervals of 8—12 hours for best results.
>
> Also note that your bot will not be able to send more than 20 messages per minute to the same group. 
>
> If you go over the limit, you'll start getting `429` errors. For more details, refer Whatsapp Api [FAQ](https://faq.whatsapp.com/).

```php
use Illuminate\Support\Facades\Notification;

// Recipients can be an array of numbers or collection of notifiable entities.
Notification::send($recipients, new InvoicePaid());
```

## Available Methods

### Shared Methods

> These methods are optional and shared across all the API methods.

- `to(int|string $number)`: Recipient's number.
- `session(string $session)`: Session if you wish to override the default session for a specific notification.
- `options(array $options)`: Allows you to add additional params or override the payload.
- `getPayloadValue(string $key)`: Get payload value for given key.

### Whatsapp Message methods

For more information on supported parameters, check out these [docs](https://orkestral.github.io/venom/index.html).

- `content(string $content, int $limit = null)`: Notification message, supports markdown. For more information on supported markdown styles, check out these [docs](https://faq.whatsapp.com/general/chats/how-to-format-your-messages/?lang=pt_br).
- `view(string $view, array $data = [], array $mergeData = [])`: (optional) Blade template name with Whatsapp supported Markdown syntax content if you wish to use a view file instead of the `content()` method.
- `chunk(int $limit = 4096)`: (optional) Message chars chunk size to send in parts (For long messages). Note: Chunked messages will be rate limited to one message per second to comply with rate limitation requirements from Whatsapp.

### Whatsapp Location methods

- `latitude(float|string $latitude)`: Latitude of the location.
- `longitude(float|string $longitude)`: Longitude of the location.
- `title(string $title)`: Title of location
- `description(string $description)`: description of location

### Whatsapp File methods

- `content(string $content)`: (optional) File caption, supports markdown. For more information on supported markdown styles, check out these [docs](https://orkestral.github.io/venom/interfaces/SendFileResult.html).
- `view(string $view, array $data = [], array $mergeData = [])`: (optional) Blade template name with Whatsapp supported HTML or Markdown syntax content if you wish to use a view file instead of the `content()` method.
- `file(string|resource|StreamInterface $file, string $type, string $filename = null)`: Local file path or remote URL, `$type` of the file (Ex:`photo`, `audio`, `document`, `video`, `animation`, `voice`, `video_note`) and optionally filename with extension. Ex: `sample.pdf`. You can use helper methods instead of using this to make it easier to work with file attachment.
- `photo(string $file)`: Helper method to attach a photo.
- `audio(string $file)`: Helper method to attach an audio file (MP3 file).
- `document(string $file, string $filename = null)`: Helper method to attach a document or any file as document.
- `video(string $file)`: Helper method to attach a video file.
- `animation(string $file)`: Helper method to attach an animated gif file.


### Whatsapp Contact methods

- `phoneNumber(string $phoneNumber)`: Contact phone number.
- `name(string $name)`: Full name.
- `firstName(string $firstName)`: (optional if you use name param) Contact first name.
- `lastName(string $lastName)`: (optional) Contact last name.

## Simple Whatsapp Api

For simple use, please consider using [whatsapp-api](https://orkestral.github.io/venom/index.html) instead.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


## Security

If you discover any security related issues, please email felipe.devops@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Felipe D. Teodoro][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/felipedamacenoteodoro/laravel-whatsapp-notification-channel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/felipedamacenoteodoro/laravel-whatsapp-notification-channel.svg?style=flat-square

[link-repo]: https://github.com/felipedamacenoteodoro/laravel-whatsapp-notification-channel
[link-packagist]: https://packagist.org/packages/felipedamacenoteodoro/laravel-whatsapp-notification-channel
[link-author]: https://www.linkedin.com/in/felipedamacenoteodoro
[link-contributors]: ../../contributors
[link-notification-facade]: https://laravel.com/docs/8.x/notifications#using-the-notification-facade
[link-on-demand-notifications]: https://laravel.com/docs/8.x/notifications#on-demand-notifications
