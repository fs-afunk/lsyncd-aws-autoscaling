<?php
/**
 * lsyncd-aws-autoscaling
 *
 * Lsyncd auto configuration that works with Amazon Web Services (AWS) Auto Scaling
 *    
 * This project is shamelessly stolen from https://github.com/uzyn, and modified 
 * to work with application type load balancers and the aws-php-sdk 3.
 *
 * @author       U-Zyn Chua <uzyn@zynesis.com>
 * @author       Alex Funk <afunk@firstscribe.com>
 * @copyright    Copyleft
 * @link         http://www.firstscribe.com
 * @license      MIT License
 */

use Liip\ProcessManager\ProcessManager;

/**
 * Check if slaves fingerprint has changed
 *
 * @param array $slaves Array of slaves with EC2 instance ID as array key
 * @param string $fileLocation Location of file storing slaves fingerprint
 * @return boolean
 */
function hasSlavesChanged($slaves, $fileLocation)
{
    $old = getSavedSlaves($fileLocation);
    sort($old);
    sort($slaves);

    if ($old == $slaves) {
        return false;
    }

    return true;
}

/**
 * Save the latest slaves fingerprint
 *
 * @param array $slaves Array of slaves with EC2 instance ID as array key
 * @param string $fileLocation Location of file storing slaves fingerprint
 * @return boolean
 */
function saveSlaves($slaves, $fileLocation)
{
    if (file_exists($fileLocation) && !is_writable($fileLocation)) {
        trigger_error($fileLocation . ' is not writable.', E_USER_ERROR);
    }

    return file_put_contents($fileLocation, serialize($slaves));
}

/**
 * Read and return last saved slaves fingerprint from $fileLocation
 *
 * @param string $fileLocation Location of file storing slaves fingerprint
 * @return array Array containing slave IDs
 */
function getSavedSlaves($fileLocation)
{
    $old = array();
    if (file_exists($fileLocation)) {
        $old = unserialize(file_get_contents($fileLocation));
    }

    return $old;
}

/**
 * Check if Lsyncd is still alive
 * If it is not, start it
 *
 * @param array $APP_CONF Application configuration
 * @return void
 */
function keepLsyncdAlive($APP_CONF)
{
    $processManager = new ProcessManager();
    $pidFile = $APP_CONF['data_dir'] . 'lsyncd.pid';

    echo "Checking if Lsyncd is still running.\n";

    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);

        if ($processManager->isProcessRunning($pid)) {
            echo "Lsyncd is still running fine.\n";
            return;
        }
    }

    echo "Lsyncd is not active.\n";
    echo "Starting Lsyncd.\n";
    startLsyncd($APP_CONF);
}

function restartLsyncd($APP_CONF)
{
    $processManager = new ProcessManager();
    $pidFile = $APP_CONF['data_dir'] . 'lsyncd.pid';

    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);

        if ($processManager->isProcessRunning($pid)) {
            echo "Stopping existing Lsyncd.\n";
            $processManager->killProcess($pid);
        }
    }

    echo "Starting Lsyncd.\n";
    startLsyncd($APP_CONF);
}

function startLsyncd($APP_CONF)
{
    $processManager = new ProcessManager();
    $pidFile = $APP_CONF['data_dir'] . 'lsyncd.pid';

    $command = $APP_CONF['path_to_lsyncd'] . ' ' . $APP_CONF['data_dir'] . 'lsyncd.conf.lua';
    if (isset($APP_CONF['dry_run']) && $APP_CONF['dry_run']) {
        $command = 'sleep 60';
    }

    $pid = $processManager->execProcess($command);
    file_put_contents($pidFile, $pid);
    echo "Lsyncd started. Pid: $pid.\n";

    return;
}

function stopLsyncd($APP_CONF)
{
    $processManager = new ProcessManager();
    $pidFile = $APP_CONF['data_dir'] . 'lsyncd.pid';

    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);

        if ($processManager->isProcessRunning($pid)) {
            echo "Stopping existing Lsyncd.\n";
            $processManager->killProcess($pid);
        }
        unlink($pidFile);
    }
    
    return;
}