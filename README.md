# You Shall Not Parse

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A lightweight PHP code obfuscator designed to make project logic harder to reverse-engineer.  
**You Shall Not Parse** renames PHP functions, classes, variables & ~~file names~~, and updates any includes/requires accordingly. It should not alter anything outside PHP tags, so HTML, CSS, Javascript etc should be left alone.

**You Shall Not Parse** is not designed to make your project harder to edit or copy, but to make understanding the logic far harder.

## ⚠️ Early Release Warning

This is an early version of the software, built for a specific personal use case. You're welcome to use it, but it should be considered alpha quality at best. While the core functionality works in my specific scenario, it has only undergone light testing. Please:

- Test thoroughly in a safe environment before using on production code
- Always maintain backups of your source code
- Verify the obfuscated output works as expected
- Report any issues via GitHub

## 🚀 Features

- Obfuscates PHP file names, variables, classes & functions into random strings
- Stores consistent mappings across files in a JSON file
- Only processes code within PHP tags, preserves HTML/CSS/JavaScript
- Keeps original files untouched — writes to a separate output folder
- Skips specified files, folders, variables, classes & functions
- ~~Rename all, some or none of your files~~
- Updates all references (`include`, `require`, etc.) to match renamed files
- ~~Optional comment and whitespace removal~~

## 🎯 Before & After Examples

### Original Code

```php
<?php
require_once 'test2.php';
require_once 'test3.php';

//user manager
class UserManager {
    private $users = [];
    private $validator;
    private $notifier;

    public function __construct() {
        $this->validator = new UserValidator();
        $this->notifier = new UserNotifier();
    }

    //add user
    public function addUser($name, $email) {
        $this->validator->validateUser($name, $email);

        $this->users[] = [
            'name' => $name,
            'email' => $email,
            'timestamp' => time()
        ];

        $this->notifier->notifyUserCreated($name, $email);
    }

    public function getUsers() {
        return $this->users;
    }

    public function getNotificationLog() {
        return $this->notifier->getNotificationLog();
    }
}

$userManager = new UserManager();
try {
    $userManager->addUser("John Doe", "john@example.com");
    $userManager->addUser("Jane Smith", "jane@example.com");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
    <style>
        .user-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem;
        }
        .user-card {
            border: 1px solid #ccc;
            padding: 1rem;
            border-radius: 4px;
        }
        .notification-log {
            margin-top: 2rem;
            padding: 1rem;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="user-grid">
        <?php foreach ($userManager->getUsers() as $user): ?>
            <div class="user-card">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <button onclick="greetUser('<?php echo htmlspecialchars($user['name']); ?>')">
                    Greet
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="notification-log">
        <h3>Notification Log</h3>
        <ul>
            <?php foreach ($userManager->getNotificationLog() as $notification): ?>
                <li>
                    <?php echo htmlspecialchars($notification['message']); ?>
                    (<?php echo date('Y-m-d H:i:s', $notification['timestamp']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        function greetUser(name) {
            const message = `Hello, ${name}!`;
            alert(message);
            console.log('Greeted user:', name);
        }
    </script>
</body>
</html>
```

### After Obfuscation (strip_whitespace: false, strip_comments: true)

```php
<?php
require_once 'file_be5df40dbdbaec0b.php';
require_once 'file_a55e5905073edba0.php';

class c_70e4fd4be7e9645b {
    private $v_77518d70cd6ae26b = [];
    private $v_94d7d252af7b3292;
    private $v_d9cd8f88faa67812;

    public function __construct() {
        $this->v_94d7d252af7b3292 = new c_ab14ecf9610575ef();
        $this->v_d9cd8f88faa67812 = new c_05f15cb2b04e05fe();
    }

    public function fn_ad283b581f117b48($v_864d9986bc530e72, $v_75a436c68e01eaa6) {
        $this->v_94d7d252af7b3292->fn_58b0c65d96acb701($v_864d9986bc530e72, $v_75a436c68e01eaa6);

        $this->v_77518d70cd6ae26b[] = [
            "v_864d9986bc530e72" => $v_864d9986bc530e72,
            "v_75a436c68e01eaa6" => $v_75a436c68e01eaa6,
            "v_3daee571b4830a8c" => time()
        ];

        $this->v_d9cd8f88faa67812->fn_2adbd322ef211438($v_864d9986bc530e72, $v_75a436c68e01eaa6);
    }

    public function fn_e287fc6fc18d6c82() {
        return $this->v_77518d70cd6ae26b;
    }

    public function fn_5cb7146c29623005() {
        return $this->v_d9cd8f88faa67812->fn_5cb7146c29623005();
    }
}

$v_17737620683f6db8 = new c_70e4fd4be7e9645b();
try {
    $v_17737620683f6db8->fn_ad283b581f117b48("John Doe", "john@example.com");
    $v_17737620683f6db8->fn_ad283b581f117b48("Jane Smith", "jane@example.com");
} catch (Exception $v_20c59d72da350d4e) {
    echo "Error: " . $v_20c59d72da350d4e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
    <style>
        .user-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem;
        }
        .user-card {
            border: 1px solid #ccc;
            padding: 1rem;
            border-radius: 4px;
        }
        .notification-log {
            margin-top: 2rem;
            padding: 1rem;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="user-grid">
        <?php foreach ($v_17737620683f6db8->fn_e287fc6fc18d6c82() as $v_f29748ba25a39a47): ?>
            <div class="user-card">
                <h3><?php echo htmlspecialchars($v_f29748ba25a39a47["v_864d9986bc530e72"]); ?></h3>
                <p><?php echo htmlspecialchars($v_f29748ba25a39a47["v_75a436c68e01eaa6"]); ?></p>
                <button onclick="greetUser('<?php echo htmlspecialchars($v_f29748ba25a39a47["v_864d9986bc530e72"]); ?>')">
                    Greet
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="notification-log">
        <h3>Notification Log</h3>
        <ul>
            <?php foreach ($v_17737620683f6db8->fn_5cb7146c29623005() as $v_094ed47740f22d83): ?>
                <li>
                    <?php echo htmlspecialchars($v_094ed47740f22d83["v_3e0a5dfd4218705e"]); ?>
                    (<?php echo date('Y-m-d H:i:s', $v_094ed47740f22d83["v_3daee571b4830a8c"]); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        function greetUser(name) {
            const message = `Hello, ${name}!`;
            alert(message);
            console.log('Greeted user:', name);
        }
    </script>
</body>
</html>
```

### After Obfuscation (strip_whitespace: true, strip_comments: true)

```php
<?php require_once 'file_f106b5cd499ef6df.php'; require_once 'file_72ac14d8c7ca337e.php'; class c_bd3d9fa23c40fd8c { private $v_7f7b721d9f49fe9a = []; private $v_4d0c2c6a7b2b0ef2; private $v_24e9e268fbac9c1f; public function __construct() { $this->v_4d0c2c6a7b2b0ef2 = new c_f8f47c37f95208ed(); $this->v_24e9e268fbac9c1f = new c_9ccf7e08c7bf9460(); } public function fn_7570dddd1033e04a($v_3485301e291b7180, $v_72fcf8f5a59e9a7e) { $this->v_4d0c2c6a7b2b0ef2->fn_287cc354c565e3f9($v_3485301e291b7180, $v_72fcf8f5a59e9a7e); $this->v_7f7b721d9f49fe9a[] = [ "v_3485301e291b7180" => $v_3485301e291b7180, "v_72fcf8f5a59e9a7e" => $v_72fcf8f5a59e9a7e, "v_4bae6e2abf3a752d" => time() ]; $this->v_24e9e268fbac9c1f->fn_97f1583b98117b22($v_3485301e291b7180, $v_72fcf8f5a59e9a7e); } public function fn_fb4b5d5db735fa81() { return $this->v_7f7b721d9f49fe9a; } public function fn_2744f51a6c4213e4() { return $this->v_24e9e268fbac9c1f->fn_2744f51a6c4213e4(); } } $v_0999098553c8c5e7 = new c_bd3d9fa23c40fd8c(); try { $v_0999098553c8c5e7->fn_7570dddd1033e04a("John Doe", "john@example.com"); $v_0999098553c8c5e7->fn_7570dddd1033e04a("Jane Smith", "jane@example.com"); } catch (Exception $v_d6ae5575a20bd6e9) { echo "Error: " . $v_d6ae5575a20bd6e9->getMessage(); } ?> <!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <title>Test Page</title> <style> .user-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1rem; } .user-card { border: 1px solid padding: 1rem; border-radius: 4px; } .notification-log { margin-top: 2rem; padding: 1rem; background: } </style> </head> <body> <div class="user-grid"> <?php foreach ($v_0999098553c8c5e7->fn_fb4b5d5db735fa81() as $v_2eb07b87d04401c3): ?> <div class="user-card"> <h3><?php echo htmlspecialchars($v_2eb07b87d04401c3["v_3485301e291b7180"]); ?></h3> <p><?php echo htmlspecialchars($v_2eb07b87d04401c3["v_72fcf8f5a59e9a7e"]); ?></p> <button onclick="greetUser('<?php echo htmlspecialchars($v_2eb07b87d04401c3["v_3485301e291b7180"]); ?>')"> Greet </button> </div> <?php endforeach; ?> </div> <div class="notification-log"> <h3>Notification Log</h3> <ul> <?php foreach ($v_0999098553c8c5e7->fn_2744f51a6c4213e4() as $v_1328767d3759bb42): ?> <li> <?php echo htmlspecialchars($v_1328767d3759bb42["v_a98395a453effbb3"]); ?> (<?php echo date('Y-m-d H:i:s', $v_1328767d3759bb42["v_4bae6e2abf3a752d"]); ?>) </li> <?php endforeach; ?> </ul> </div> <script> function greetUser(name) { const message = `Hello, ${name}!`; alert(message); console.log('Greeted user:', name); } </script> </body> </html>
```

## 📦 Cloning the Project

```bash
git clone https://github.com/peskybogle/YouShallNotParse.git
cd YouShallNotParse
```

## 🔧 Installation

1. Clone this repository
2. Run `composer install`
3. Configure `ysnp.config.json` with your settings
4. Run `php ysnp.php`

## ⚙️ Configuration

### ysnp.config.json

```json
{
  "source_directory": "/path/to/your/php/project", // Directory containing PHP files to obfuscate
  "destination_directory": "/path/to/output", // Directory where obfuscated files will be written
  "ignore_directories": [], //ignores everything in a directory. e.g. ["admin", "backups"]
  "ignore_files": [], //totally ignores a file and everything in it. e.g. ["admin.php", "register.php"]
  "skip_rename_files": [], //processes a file, but doesn't rename it, even if rename_files is set to true. e.g. ["index.php", "app.php"]
  "skip_variables": [], //e.g. ["userdata", "companydetails"]
  "skip_functions": [], //e.g. ["register", "signin"]
  "skip_classes": [], //e.g. ["customers"]
  "strip_comments": true, //true or false
  "strip_whitespace": false, //true or false
  "rename_files": false //true or false
}
```

### File Renaming

The file renaming system provides granular control over how your PHP files are processed:

- `rename_files`: Master switch for file renaming functionality

  - When `false`: All files retain their original names
  - When `true`: Files are renamed using secure hex identifiers (e.g., `file_67c1f19b277c27c0.php`)

- `skip_rename_files`: Array of files to exclude from renaming
  - Useful for framework entry points and public-facing files
  - Paths can be relative to source directory
  - Files listed here retain original names regardless of `rename_files` setting

File renaming is particularly valuable when:

- Protecting proprietary module names
- Obscuring application architecture
- Preventing direct file access attempts

Note: File renaming updates all corresponding `require`/`include` statements automatically to maintain dependencies.

### ysnp.safety.json

Contains functions, variables and classes that should never be renamed. You shouldn't need to modify this file.

## 📁 Output

The tool generates `file_name_map.json` containing mappings of original names to obfuscated names for:

- Variables
- Functions
- Classes
- Files

## ⚠️ The disclaimer

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## 👽 Project by PeskyBogle

You Shall Not Parse 🧙🏻‍♂️
