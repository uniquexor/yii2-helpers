<?php
    namespace unique\yii2helpers\interfaces;

    interface ConsoleInterface {

        /**
         * Prints a string to STDOUT.
         *
         * @param string $string the string to print
         * @return int|bool Number of bytes printed or false on error
         */
        public function stdout( string $string );

        /**
         * Prints a string to STDERR.
         *
         * @param string $string the string to print
         * @return int|bool Number of bytes printed or false on error
         */
        public function stderr( string $string );
    }