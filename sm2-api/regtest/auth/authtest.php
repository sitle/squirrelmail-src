<?php

/********************************************************************
* THIS IS THE CODE REQUIRED TO LOAD ZOOKEEPER                      *
*                                                                  *
* 1. session_start is called from within zkld                      *
* 2. the call to require_once loads the necessary class and        *
*    function definitions required to use zookeeper.               *
* 3. the ZkLoader object created is the core object used to        *
*    access the services and functionality provided by zookeeeper. *
*                                                                  *
* Of course, all of this will be documented better, later.... :)   *
********************************************************************/

$time_ini = time();     // Let's do some performance test

$authtest_version = '$Id$';
$zkhome = '../../../sm2-api';

/* Set the test username and password. */
if ( !isset( $test_user ) ) {
    $test_user = 'captbunzo';
}
if ( !isset( $test_pass ) ) {
    $test_pass = 'bunz-r-us';
}
if ( !isset( $test_timeout ) ) {
    $test_timeout = 10;
}

require_once( $zkhome . '/ZkLoader.php' );

$zkld = new ZkLoader('zktesting',$zkhome);

$authoptions = array('maxlogin' => 300,
                    'maxidle' => 10 );
$authority['test'] = $zkld->loadService( 'auth', $authoptions, 'test', zkSS() );

$imap_opt = array( 'maxlogin' => 300,
                'maxidle' => 10,
                'connector' => array( 'host' => 'localhost',
                                        'port' => 143,
                                        'timeout' => $test_timeout ) );
$authority['imap'] = $zkld->loadService( 'auth', $imap_opt, 'imap', zkSS() );

$pop3_opt = array( 'maxlogin' => 300,
                'maxidle' => 10,
                'connector' => array( 'host' => 'localhost',
                                        'port' => 110,
                                        'timeout' => $test_timeout ) );
$authority['pop3'] = $zkld->loadService( 'auth', $pop3_opt, 'pop3', zkSS() );

$ftp_opt = array( 'maxlogin' => 300,
                'maxidle' => 10,
                'connector' => array( 'host' => 'localhost',
                                        'port' => 21,
                                        'timeout' => $test_timeout ) );
$authority['ftp'] = $zkld->loadService( 'auth', $ftp_opt, 'ftp', zkSS() );

$sql_opt = array( 'maxlogin' => 300,
                'maxidle' => 10,
                'connector' => array( 'host' => 'localhost',
                                        'db' => 'mysql',
                                        'user' => 'root',
                                        'pass' => '' ) );
$authority['mysql'] = $zkld->loadService( 'auth', $sql_opt, 'mysql', zkSS() );

$authority['php_imap'] = $zkld->loadService( 'auth', $imap_opt, 'php_imap', zkSS() );

$html = $zkld->loadService( 'html', NULL, 'html40', zkSS() );

/*** END ZOOKEEPER INITIAL LOAD CODE ***/

$html->title = 'Testing Service auth';
$html->head_extras = '<LINK REL="stylesheet" TYPE="text/css" HREF="authtest.css">';
$html->header();
$html->flush( $html->h( $html->title ) . $html->tag( 'p', 'Test Version: ' . $authtest_version ) );

$html->tag_options['form']['action'] = 'authtest.php';
$html->tag_options['form']['method'] = 'POST';

$html->flush(
    $html->tag( 'form',
        $html->tag( 'table',
            $html->tag( 'tr',
                $html->tag( 'td', 'Name' ) .
                $html->tag( 'td',
                    $html->tag( 'input', '', array( 'type' => 'text',
                                                    'name' => 'test_user',
                                                    'value' => $test_user,
                                                    'size' => 15 ) )
                    )
                ) .
            $html->tag( 'tr',
                $html->tag( 'td', 'Password' ) .
                $html->tag( 'td',
                    $html->tag( 'input', '', array( 'type' => 'password',
                                                    'name' => 'test_pass',
                                                    'value' => $test_pass,
                                                    'size' => 15 ) )
                    )
                ) .
            $html->tag( 'tr',
                $html->tag( 'td',
                    $html->tag( 'input','', array( 'type' => 'submit' )  ) .
                    $html->tag( 'input','', array( 'type' => 'reset' ) )
                    , array( 'colspan' => 2 ) )
                )
            )
        )
    );

$html->tag_options['table']['bgcolor']= '#e0e0e0';

foreach( $authority as $auth ) {

    $html->flush( $html->h( 'Testing Module: ' . $auth->mod->name, 2 ) );
    $html->flush( $html->h( 'Service: ' . $auth->ver, 6 ) );
    $html->flush( $html->h( 'Module: ' . $auth->mod->ver, 6 ) );

    if ( $auth->checkLogin( $test_user, $test_pass ) ) {
        $html->flush (
            $html->tag( 'li', 'You are logged in.' ) .
            $html->tag( 'table', $html->tag( 'tr', $html->tag( 'td',
                $html->tag( 'table',
                    $html->tag( 'tr',
                        $html->tag( 'th', 'USERNAME' ) .
                        $html->tag( 'td', $auth->getUsername() )
                        ) .
                    $html->tag( 'tr',
                        $html->tag( 'th', 'PASSWORD' ) .
                        $html->tag( 'td', $auth->getPassword() )
                        )
                    )
                ) ), array( 'bgcolor' => 'black',
                            'cellspacing' => '1' ) )
            );
    } else {
        $html->buffer .= $html->tag( 'li', 'You are not logged in. Attempting login.' );
        if($auth->login($test_user, $test_pass)) {
            $html->buffer .= $html->tag( 'blockquote',  'login succeeded!!!!!' );
        $html->flush (
            $html->tag( 'li', 'You are logged in.' ) .
            $html->tag( 'table', $html->tag( 'tr', $html->tag( 'td',
                $html->tag( 'table',
                    $html->tag( 'tr',
                        $html->tag( 'th', 'USERNAME' ) .
                        $html->tag( 'td', $auth->getUsername() )
                        ) .
                    $html->tag( 'tr',
                        $html->tag( 'th', 'PASSWORD' ) .
                        $html->tag( 'td', $auth->getPassword() )
                        )
                    )
                ) ), array( 'bgcolor' => 'black',
                            'cellspacing' => '1' ) )
            );

        } else {
            $html->buffer .= $html->tag( 'blockquote',  'login failed!!!!!' );
        }

        if ($auth->checkLogin()) {
            $html->buffer .= $html->tag( 'li', 'You are logged in.' );
        } else {
            $html->buffer .= $html->tag( 'li', 'You are not logged in.');
        }
    }

    $html->buffer .= $html->tag( 'li', 'Time: ' . time() ).
                    $html->tag( 'li', 'Idles: ' . $auth->idles ).
                    $html->tag( 'li', 'Expires: ' . $auth->expires );

    /* Play around with the authentication service, doing some general testing. */
    if ($auth->loginExpired()) {
        $html->buffer .= $html->tag( 'li', 'Your login has expired.' );
    } else {
        $html->buffer .= $html->tag( 'li', 'Your login has not yet expired.' );
    }

    if ($auth->loginIdled()) {
        $html->buffer .= $html->tag( 'li', 'Your login has idled.' );
    } else {
        $html->buffer .= $html->tag( 'li', 'Your login has not yet idled.' );
    }


    $html->flush( $html->tag( 'li', 'Banner: ' . $auth->banner ) );
    $html->flush( $html->tag( 'li', 'Extra info: ' . $auth->info ) );
}

echo '<br>Time Elapsed ' . ( time() - $time_ini );
?>