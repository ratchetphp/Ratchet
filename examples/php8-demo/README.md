# PHP 8 WebSocket Example (Ratchet)

This example provides a minimal WebSocket **server** and **client** compatible with **PHP 8.3+** (including PHP 8.4), demonstrating a modern and fully working setup with Ratchet.

It showcases:

* A simple Ratchet WebSocket server (tested with Ratchet >= 0.4)
* A standalone PHP client using the **`ratchet/pawl`** library
* Full compatibility with Linux and macOS

> **Note**
> The client uses `ratchet/pawl`, which is **not a dependency of Ratchet itself**.
> We keep it separate to avoid introducing new PHP version requirements into Ratchet’s core `composer.json`.

---

## Requirements

* PHP `>=7.4` (required by `ratchet/pawl`)
* Ratchet (already installed in the main project)

---

## How to run

### 1. Install Ratchet dependencies (from the project root)

```bash
composer install
```

### 2. Move into the `php8-demo` folder

```bash
cd examples/php8-demo
```

### 2. Install the client dependency (inside the `php8-demo` folder)

```bash
composer install
```

**Note:**
This creates a dedicated `composer.lock` and `vendor` folder inside the example folder, keeping the demo self-contained and avoiding new dependencies in Ratchet itself.

### 3. Start the WebSocket server

```bash
php server.php
```

You should see:

```
WebSocket server started at ws://127.0.0.1:8080
```

### 4. Run the PHP client

```bash
php client.php
```

Expected output:

```
Connected to WebSocket server
Message sent: Hello from PHP 8 client!
Message received from server: Client <number> says: Hello from PHP 8 client!
Connection closed
```

---

## Tested with

| OS    | PHP Version |
| ----- | ----------- |
| Linux | 8.3.x       |
| macOS | 8.4.x       |

This example ensures full compatibility with modern PHP versions without affecting Ratchet’s backward compatibility.
