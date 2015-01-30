<!DOCTYPE html>
<html>
    <head>
        <title>Configure Net-M Emulator</title>
    </head>

    <body>
        <form method="GET">
            Campaign Link: <input type="text" name="link"><br>
            MSISDN: <input type="text" name="msisdn" value="<?php echo '0049' . rand(pow(10, 9), pow(10, 10)-1); ?>"><br>
            Balance: <input type="text" name="balance" value="100"><br>

            Operator:
            <select name="operator">
                <option value="tmobile">T-Mobile</option>
                <option value="eplus">E-Plus</option>
            </select>
            <br>

            WAP: <input type="radio" name="flow" value="wap" checked>
            WEB: <input type="radio" name="flow" value="web">
            <br>

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

    $rid = base64_encode(json_encode($config));
    $link = $_GET['link'] . '&rid=' . $rid;

    echo sprintf("<a target=\"_blank\" href=\"%s\">%s</a>", $link, $link);
}

?>
        </p>

    </body>
</html>

