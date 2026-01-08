# Laravel WebSocket with Real-time Notifications

This project demonstrates how to set up and use WebSocket notifications in Laravel using Laravel WebSockets (beyondcode/laravel-websockets) with Vue.js frontend.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Starting the WebSocket Server](#starting-the-websocket-server)
- [Creating Broadcast Events](#creating-broadcast-events)
- [Listening to Events in Frontend](#listening-to-events-in-frontend)
- [Examples](#examples)
- [Troubleshooting](#troubleshooting)

## Prerequisites

- PHP 8.1+ (Note: PHP 8.4 compatibility fix applied for statistics logger)
- Laravel 10+
- Node.js and npm
- Composer

## Installation

### 1. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 2. Configure Environment

Add these to your `.env` file:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=laravel-web-socket-key
PUSHER_APP_SECRET=laravel-web-socket-secret
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

### 3. Run Migrations

```bash
php artisan migrate
```

This will create the `websockets_statistics_entries` table (though statistics are disabled by default).

### 4. Build Frontend Assets

```bash
npm run dev
# or for production
npm run build
```

## Configuration

### Broadcasting Configuration

The broadcasting driver is configured in `config/broadcasting.php`. Make sure `BROADCAST_DRIVER=pusher` is set in your `.env`.

### WebSocket Configuration

WebSocket settings are in `config/websockets.php`:

- **Port**: Default is `6001` (configurable via `LARAVEL_WEBSOCKETS_PORT`)
- **Statistics**: Disabled by default (to avoid PHP 8.4 compatibility issues)
- **Apps**: Configured via environment variables

### Frontend Configuration

Echo is configured in `resources/js/bootstrap.js`:

```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'laravel-web-socket-key',
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    cluster: 'mt1',
    enabledTransports: ['ws', 'wss'],
});
```

## Starting the WebSocket Server

Start the Laravel WebSocket server:

```bash
php artisan websockets:serve
```

The server will start on port `6001` by default. Keep this terminal running.

**Note**: You need to run this command in a separate terminal while your Laravel application is running.

## Creating Broadcast Events

### 1. Create an Event

Create a new event that implements `ShouldBroadcast`:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('Notification'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'notification' => $this->notification,
        ];
    }
}
```

### 2. Broadcast the Event

You can broadcast events from anywhere in your application:

```php
// From a controller
event(new \App\Events\NotificationReceived('Hello World!'));

// From a route
Route::get('/notify', function () {
    event(new \App\Events\NotificationReceived('Test notification'));
    return response()->json(['message' => 'Notification sent']);
});
```

### 3. Channel Types

#### Public Channels

```php
public function broadcastOn(): array
{
    return [
        new Channel('Notification'),
    ];
}
```

#### Private Channels (Requires Authentication)

```php
public function broadcastOn(): array
{
    return [
        new PrivateChannel('user.' . $this->user->id),
    ];
}
```

#### Presence Channels (User Presence)

```php
public function broadcastOn(): array
{
    return [
        new PresenceChannel('chat'),
    ];
}
```

## Listening to Events in Frontend

### Vue.js Example

```vue
<template>
    <div>
        <h1>Notifications</h1>
        <p>{{ message }}</p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            message: 'Waiting for notifications...',
        }
    },
    mounted() {
        // Listen to a public channel
        window.Echo.channel('Notification')
            .listen('NotificationReceived', (e) => {
                console.log('Notification received:', e.notification);
                this.message = e.notification;
            });
    },
    beforeDestroy() {
        // Leave channel when component is destroyed
        window.Echo.leave('Notification');
    }
}
</script>
```

### Listening to Private Channels

```javascript
// Private channel (requires authentication)
window.Echo.private('user.' + userId)
    .listen('NotificationReceived', (e) => {
        console.log(e.notification);
    });
```

### Listening to Presence Channels

```javascript
// Presence channel
window.Echo.join('chat')
    .here((users) => {
        console.log('Users here:', users);
    })
    .joining((user) => {
        console.log('User joining:', user);
    })
    .leaving((user) => {
        console.log('User leaving:', user);
    })
    .listen('MessageSent', (e) => {
        console.log('Message:', e.message);
    });
```

### Plain JavaScript Example

```javascript
// Listen to channel
Echo.channel('Notification')
    .listen('NotificationReceived', (e) => {
        console.log('Notification:', e.notification);
        document.getElementById('message').textContent = e.notification;
    });

// Leave channel
Echo.leave('Notification');
```

## Examples

### Example 1: Simple Notification

**Backend** (`routes/web.php`):
```php
Route::get('/test', function () {
    event(new \App\Events\NotificationReceived('Hello World!'));
    return response()->json(['message' => 'Notification sent']);
});
```

**Frontend** (`resources/js/components/HomeComponent.vue`):
```vue
<template>
    <div>
        <h1>Home Component</h1>
        <p>{{ message }}</p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            message: 'Hello World from Vue',
        }
    },
    mounted() {
        window.Echo.channel('Notification')
            .listen('NotificationReceived', (e) => {
                this.message = e.notification;
            });
    }
}
</script>
```

### Example 2: User-Specific Notifications

**Event**:
```php
class UserNotification implements ShouldBroadcast
{
    public $user;
    public $message;

    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }
}
```

**Frontend**:
```javascript
window.Echo.private('user.' + userId)
    .listen('UserNotification', (e) => {
        console.log('Personal notification:', e.message);
    });
```

### Example 3: Real-time Chat

**Event**:
```php
class MessageSent implements ShouldBroadcast
{
    public $message;
    public $user;

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat'),
        ];
    }
}
```

**Frontend**:
```javascript
window.Echo.join('chat')
    .here((users) => {
        console.log('Online users:', users);
    })
    .listen('MessageSent', (e) => {
        addMessageToChat(e.message, e.user);
    });
```

## Troubleshooting

### WebSocket Server Won't Start

1. **Check if port 6001 is available**:
   ```bash
   lsof -i :6001
   ```

2. **Check PHP version compatibility**:
   - PHP 8.4: Statistics logger is disabled by default (using `NullStatisticsLogger`)
   - For other versions, you can enable statistics in `config/websockets.php`

3. **Check environment variables**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Frontend Can't Connect

1. **Check WebSocket server is running**:
   ```bash
   php artisan websockets:serve
   ```

2. **Verify Echo configuration** in `resources/js/bootstrap.js`:
   - `wsHost` should match your domain
   - `wsPort` should be `6001`
   - `key` should match `PUSHER_APP_KEY` in `.env`

3. **Check browser console** for connection errors

4. **Rebuild frontend assets**:
   ```bash
   npm run dev
   ```

### Events Not Broadcasting

1. **Check broadcast driver**:
   ```env
   BROADCAST_DRIVER=pusher
   ```

2. **Verify event implements `ShouldBroadcast`**

3. **Check channel authorization** for private/presence channels in `routes/channels.php`

4. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

### CORS Issues

If you're accessing from a different domain, configure CORS in `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://your-domain.com',
],
```

## Additional Resources

- [Laravel Broadcasting Documentation](https://laravel.com/docs/broadcasting)
- [Laravel WebSockets Package](https://beyondco.de/docs/laravel-websockets)
- [Laravel Echo Documentation](https://laravel.com/docs/broadcasting#client-side-installation)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
