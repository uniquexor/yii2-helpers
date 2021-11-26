<?php
    namespace unique\yii2helpers\events;

    use unique\events\interfaces\EventObjectInterface;
    use unique\events\traits\EventObjectTrait;
    use yii\helpers\BaseConsole;

    /**
     * A LogEvent triggered by the {@see LogConsole::log()} method.
     */
    class LogEvent implements EventObjectInterface {

        use EventObjectTrait;

        /**
         * Text to be logged
         * @var string
         */
        protected string $text;

        /**
         * If eol symbol should be appended to the text.
         * @var bool
         */
        protected bool $eol;

        /**
         * Style constants array
         * @var array
         */
        protected array $styles;

        /**
         * @param string $text - Text to be logged
         * @param bool $eol - If true, an EOL symbol must be appended to $text
         * @param array $styles - An array of {@see BaseConsole}::* constants.
         */
        public function __construct( string $text, bool $eol = true, array $styles = [] ) {

            $this->text = $text;
            $this->eol = $eol;
            $this->styles = $styles;
        }

        /**
         * Returns text to be loggged
         * @return string
         */
        public function getText(): string {

            return $this->text;
        }

        /**
         * Returns true if EOL symbol must be appended to the logged text.
         * @return bool
         */
        public function getEol(): bool {

            return $this->eol;
        }

        /**
         * Returns an array of {@see BaseConsole}::* constants.
         * @return array
         */
        public function getStyles(): array {

            return $this->styles;
        }
    }