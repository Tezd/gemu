<!DOCTYPE html>
<html>
    <head>
        <title>Configure Net-M Emulator</title>
    </head>

    <body>
        <form method="GET">
            <p>
                Campaign Link: <input type="text" name="link">
            </p>
            <p>
                MSISDN: <input type="text" name="msisdn" value="<?php echo '0049' . rand(pow(10, 9), pow(10, 10)-1); ?>">
                Low balance: <input type="checkbox" name="low_balance" value="1"><br>
            </p>
            <p>
                Operator:
                <select name="operator">
                    <option value="1">T-Mobile</option>
                    <option value="2">Vodafone</option>
                    <option value="3">E-Plus</option>
                    <option value="4">O2</option>
                </select>
            </p>
            <p>
                WAP: <input type="radio" name="flow" value="wap" checked>
                WEB: <input type="radio" name="flow" value="web">
            </p>

            <input type="submit" name="submit" value="Generate link">
        </form>

        <p>

<?php

if (isset($_GET['submit'])) {
    $config = $_GET;
    unset($config['link']);
    unset($config['submit']);

    if ($config['flow'] == 'web') {
        unset($config['msisdn']);
    }

    $config['t'] = microtime(true);

    var_dump($config);

    $rid = base64_encode(json_encode($config));
    $link = $_GET['link'] . '&rid=' . $rid;

    echo sprintf("<a target=\"_blank\" href=\"%s\">%s</a>", $link, $link);
}

?>
        </p>

    </body>
</html>

