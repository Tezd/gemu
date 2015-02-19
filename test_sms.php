<?php

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

$link = isset($_REQUEST['link']) ? $_REQUEST['link'] : '';

if (isset($_REQUEST['msisdn']) && $_REQUEST['msisdn']) {
    $msisdn = $_REQUEST['msisdn'];

    if ($_REQUEST['flow'] == '3g') {
        $_REQUEST['msisdn'] = '0049' . substr($msisdn, 1);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>GEMU</title>
        <script src="jquery-2.1.3.min.js"></script>
    </head>

    <body>
        <p><a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">Home</a></p>
        <form id="sms-form" method="POST">
            <p>
                Country:
                <select name="country">
                    <option value="MY">Malaysia</option>
                </select>
            </p>
            <p>
                Gateway:
                <select name="gateway">
                    <option value="zeptomobile">Zeptomobile</option>
                </select>
            </p>
            <p>
                Operator:
                <select name="operator">
                    <option value="DG">Digi</option>
                </select>
            </p>
            <p>
                Shortcode:
                <select name="shortcode">
                    <option value="33293">33293</option>
                </select>
            </p>
            <p>
                MSISDN: <input type="text" name="msisdn" value="<?php echo $msisdn; ?>">
                <!-- Low balance: <input type="checkbox" name="low_balance" value="1"><br> -->
            </p>
            <p>
                Message text: <input type="text" name="text">
            </p>

            <input type="submit" name="submit" value="Send">
        </form>

        <p><?php echo $output; ?></p>

        <?php
            if (isset($_REQUEST['submit'])) {
        ?>

<hr>
<h3>Logs</h3>
<ol id="log"></ol>
<script>
</script>

        <?php
            }
        ?>


    </body>
</html>

