<?php
    $link = isset($_REQUEST['link']) ? $_REQUEST['link'] : '';

    if (isset($_REQUEST['msisdn']) && $_REQUEST['msisdn']) {
        $msisdn = $_REQUEST['msisdn'];

        if ($_REQUEST['flow'] == '3g') {
            $_REQUEST['msisdn'] = '0049' . substr($msisdn, 1);
        }
    }

    if (isset($_REQUEST['submit'])) {
        $config = array(
            't' => microtime(true),
        );

        $config = array_merge($config, $_REQUEST);

        unset($config['link']);
        unset($config['submit']);

        if (@$config['flow'] == 'wifi') {
            unset($config['msisdn']);
        }

        $rid = base64_encode(json_encode($config));

        $output = array();

        $url = $link . '&emulate=1&rid=' . $rid;
        $output[] = sprintf("<a target=\"_blank\" href=\"%s\">%s</a>\n", $url, $url);
        $output[] = sprintf("<p>MSISDN: %s</p>\n", $msisdn);

        $output = join('', $output);

        $checked = '';
    } else {
        $msisdn = '0' . rand(pow(10, 9), pow(10, 10)-1);
        $output = '';
        $checked = 'checked';
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Configure Net-M Emulator</title>
    </head>

    <body>
        <form method="POST">
            <p>
                Campaign Link:
                <br><br>
                <textarea style="width: 640px;" type="text" name="link" rows="3" cols="30"><?php echo $link; ?></textarea>
            </p>
            <p>
                MSISDN: <input type="text" name="msisdn" value="<?php echo $msisdn; ?>">
                <!-- Low balance: <input type="checkbox" name="low_balance" value="1"><br> -->
            </p>
            <p>
                <input type="radio" name="operator" value="1" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '1') ? 'checked' : ''; echo $checked; ?>> T-Mobile<br>
                <input type="radio" name="operator" value="2" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '2') ? 'checked' : ''; ?>> Vodafone<br>
                <input type="radio" name="operator" value="3" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '3') ? 'checked' : ''; ?>> E-Plus<br>
                <input type="radio" name="operator" value="4" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '4') ? 'checked' : ''; ?>> O2<br>
            </p>
            <p>
                <input type="radio" name="flow" value="wifi" <?php echo (isset($_REQUEST['flow']) && $_REQUEST['flow'] == 'wifi') ? 'checked' : ''; echo $checked; ?>> Wifi Flow<br>
                <input type="radio" name="flow" value="3g" <?php echo (isset($_REQUEST['flow']) && $_REQUEST['flow'] == '3g') ? 'checked' : '' ?>> 3G Flow<br>
            </p>

            <input type="submit" name="submit" value="Generate link">
        </form>

        <p><?php echo $output; ?></p>

        <?php
            if (isset($_REQUEST['submit'])) {
        ?>

<hr>
<h3>Logs</h3>
<ol id="log"></ol>
<script>
    var src = new EventSource('<?php echo "logs.php?rid=$rid"; ?>');

    src.onmessage = function(e) {
        console.log(e.data);
        var el = document.createElement('li');
        el.innerHTML = e.data;

        document.getElementById('log').appendChild(el);
    };
</script>

        <?php
            }
        ?>


    </body>
</html>

