<?php
spl_autoload_register( function ( $class ) {
    if ( strpos( $class, 'EDD_Adyen_' ) !== 0 ) {
        return;
    }

    $base_dir = __DIR__;
    $class_file = 'class-'.strtolower( str_replace( '_', '-', $class ) ) . '.php';

    $directories = [
        $base_dir . '/admin/',
        $base_dir . '/core/',
        $base_dir . '/gateway/',
        $base_dir . '/payments/',
    ];

    foreach ( $directories as $dir ) {
        $file = $dir . $class_file;
        if ( file_exists( $file ) ) {
            require_once $file;
            return;
        }
    }
});
