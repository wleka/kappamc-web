<?php
// Обязательно запускаем сессии в самой первой строчке кода!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Переменные для вывода сообщений на экран
$success_message = "";
$error_message = "";

// 1. ГЕНЕРИРУЕМ ТОКЕН ЗАЩИТЫ ОТ СПАМА (если его еще нет)
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// 2. ОБРАБОТКА ОТПРАВКИ ФОРМЫ (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Проверяем: совпадает ли токен из формы с токеном на сервере
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['form_token']) {
        $error_message = "You form has already sended. Please refrest page.";
    } else {
        
        $minecraft_nick = trim($_POST['minecraft_answer']);
        $minecraft_nick = htmlspecialchars($minecraft_nick);

        $discord_nick = trim($_POST['discord_answer']);
        $discord_nick = htmlspecialchars($discord_nick);

        $yo_nick = trim($_POST['yo_answer']);
        $yo_nick = htmlspecialchars($yo_nick);

        if (!empty($minecraft_nick) && !empty($discord_nick) && !empty($yo_nick)) {
            
            $webhook_url = "";

            $msg_data = [
                "content" => "🚀 **NEW PASS**\n>>> **Minecraft nick:** " . $minecraft_nick .
                             "\n**Discord** : " . $discord_nick .
                             "\n**Years old** : " . $yo_nick
            ];

            $ch = curl_init($webhook_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg_data));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_exec($ch);
            curl_close($ch);

            // УНИЧТОЖАЕМ ТОКЕН: Теперь эта форма больше никогда не сможет отправиться повторно!
            unset($_SESSION['form_token']);
            
            // Записываем флаг успеха в сессию
            $_SESSION['success_flash'] = true;

            // Перенаправляем на эту же страницу чисто методом GET
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } else {
            $error_message = "Пожалуйста, заполните все поля ввода!";
        }
    }
}

// 3. ПРОВЕРЯЕМ ФЛАГ УСПЕХА ПОСЛЕ ПЕРЕНАПРАВЛЕНИЯ
if (isset($_SESSION['success_flash'])) {
    $success_message = "Form has been sended! Please wait anwser in Discord.";
    unset($_SESSION['success_flash']); // Сразу удаляем, чтобы сообщение не висело вечно
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="index.css" rel="stylesheet">
    <title>KappaMC\pass</title>
</head>
<body>

<div class="container">

    <center>
        <div class="nav" id="nav">
            <h1 class="nav_text">KappaMC</h1>
            <a href="/"><button class="nav_button">Главная</button></a>
            <a href="/wiki/"><button class="nav_button">Википедия</button></a>
            <a href="/map/"><button class="nav_button">Карта</button></a>
            <a href="#"><button class="nav_button_active">Pass</button></a>
            <a href="https://wleku.blog"><button class="nav_button">wleku.blog</button></a>
        </div>
    </center>

    <center>
        <div class="blog">

            <h2>Q&A for get whitelist on server.</h2>
            <p class="header_text">You should be in Discord server, for get anwser.<br>
            If you don't joined in Discord, please join to Discord</p>

            <a href="https://aternos.online/discord/"><button class="nav_button">Join to Discord server</button></a>

            <br><hr><br>

            <?php if (!empty($success_message)): ?>
                <div class='result' style='color: green; font-weight: bold; margin-bottom: 20px;'><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class='error' style='color: red; font-weight: bold; margin-bottom: 20px;'><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <input type="hidden" name="token" value="<?php echo isset($_SESSION['form_token']) ? $_SESSION['form_token'] : ''; ?>">

                <label for="mc_input" class="header_text">You're nick in Minecraft.</label>
                <input type="text" id="mc_input" name="minecraft_answer" placeholder="Only lincese account." required><br><br>

                <label for="ds_input" class="header_text">You're nick in Discord.</label>
                <input type="text" id="ds_input" name="discord_answer" placeholder="You actual discord account." required><br><br>

                <label for="yo_input" class="header_text">When are you y.o?</label>
                <input type="text" id="yo_input" name="yo_answer" placeholder="Minimal years old - 13." required><br><br>

                <br>
                <input type="submit" value="Send the pass." class="nav_button">
            </form>
        </div>
    </center>

    <br><hr><br>

    <div class="about">
        <a class="copyright" href="/contact/">Информация</a>
        <p class="copyright">Данный проект не связан с Mojang Studios и не имеет отношения к официальным продуктам Microsoft.</p> 
        <p class="copyright">Designed by wleku. Development by wleku 2026</p>
    </div>

</div>

</body>
</html>