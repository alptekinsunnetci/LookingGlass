<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick@iamtelephone.com>
 * @copyright   2015 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 * @version     1.3.0
 */

/**
 * NOTE:
 *   Version 1 will continue to allow direct access to ajax.php (no CSRF protection).
 *   I recommend setting a reasonable rate-limit to overcome abuse
 */

if (isset($_GET['cmd']) && isset($_GET['host'])) {
    // define available commands
    $cmds = array('host', 'mtr', 'mtr6', 'ping', 'ping6', 'traceroute', 'traceroute6');

    // verify command
    $cmd = $_GET['cmd'];
    $host = $_GET['host'];

    if (in_array($cmd, $cmds)) {
        // include required scripts
        $required = array('LookingGlass.php', 'RateLimit.php', 'Config.php');
        foreach ($required as $val) {
            $file = 'LookingGlass/' . $val;
            if (!file_exists($file)) {
                exit("Required file $val not found.");
            }
            require $file;
        }

        // instantiate LookingGlass & RateLimit
        try {
            $rateLimit = $rateLimit ?? 0; // default to 0 if not set
            $lg = new Telephone\LookingGlass();
            $limit = new Telephone\LookingGlass\RateLimit($rateLimit);

            // check IP against database
            $limit->rateLimit($rateLimit);

            // execute command safely
            if (method_exists($lg, $cmd)) {
                $output = $lg->$cmd($host);
                if ($output) {
                    echo $output; // return command output
                    exit();
                } else {
                    exit('Command execution failed.');
                }
            } else {
                exit("Invalid command: $cmd.");
            }
        } catch (Exception $e) {
            exit('Error: ' . $e->getMessage());
        }
    } else {
        exit("Command $cmd is not allowed.");
    }
}

// report error
exit('Unauthorized request');
