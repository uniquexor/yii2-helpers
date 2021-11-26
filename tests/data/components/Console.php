<?php
    namespace unique\yii2helperstest\data\components;

    use unique\yii2helpers\interfaces\ConsoleInterface;
    use yii\helpers\BaseConsole;

    class Console implements ConsoleInterface {

        protected string $stdout = '';

        protected string $stderr = '';

        public function clear() {

            $this->stdout = '';
            $this->stderr = '';
        }

        public function getStdOut() {

            return $this->stdout;
        }

        public function getStdErr() {

            return $this->stderr;
        }

        public function stdout( $string ) {

            $this->stdout .= $string;
        }

        public function stderr( $string ) {

            $this->stderr .= $string;
        }
    }