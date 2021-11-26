<?php
    namespace unique\yii2helpers;

    use unique\events\interfaces\EventHandlingInterface;
    use unique\events\traits\EventTrait;
    use unique\yii2helpers\events\LogEvent;
    use unique\yii2helpers\interfaces\ConsoleInterface;
    use yii\helpers\Console;

    /**
     * A wrapper for logging text to console with some additional functionality.
     */
    class LogContainer implements EventHandlingInterface, ConsoleInterface {

        use EventTrait;

        const EVENT_LOG = 'log';

        const STYLE_SUCCESS = 'success';
        const STYLE_ERROR = 'error';
        const STYLE_WARNING = 'warning';

        /**
         * A console to use for logging
         * @var ConsoleInterface
         */
        protected $console;

        /**
         * If set to false, means there is already text on the current line, otherwise - means an empty line.
         * @var bool
         */
        protected bool $is_new_line = true;

        /**
         * If set to true, will perform logging. Otherwise, will gracefully exit all loging functions.
         * @var bool
         */
        protected bool $is_enabled = true;

        /**
         * EOL symbol to use.
         * @var string
         */
        protected string $eol;

        /**
         * A string to prepend to a new line.
         * @var string
         */
        public string $prepend_line = '[<datetime:Y-m-d H:i:s>] ';

        /**
         * Styles to use for {@see logSuccess()}, {@see logError()} and {@see logWarning()} methods.
         * @var array[]
         */
        public array $styles = [
            self::STYLE_SUCCESS => [ Console::FG_GREEN ],
            self::STYLE_ERROR => [ Console::BG_RED ],
            self::STYLE_WARNING => [ Console::BG_YELLOW ],
        ];

        /**
         * @param ConsoleInterface $console - A Console to use for logging.
         * @param string $eol - An end of line symbol to use. Defaults to Windows style - "\r\n"
         */
        public function __construct( ConsoleInterface $console, string $eol = "\r\n" ) {

            $this->console = $console;
            $this->eol = $eol;
        }

        /**
         * Sets logging to enabled or disabled state. In disabled state, all loging methods will gracefully exit without logging anything.
         * @param bool $is_enabled
         */
        public function setEnabled( bool $is_enabled = true ) {

            $this->is_enabled = $is_enabled;
        }

        /**
         * Disables logging - all loging methods will gracefully exit without logging anything.
         * An alias for calling {@see setEnabled}( false )
         */
        public function setDisabled() {

            $this->setEnabled( false );
        }

        /**
         * @inheritdoc
         */
        public function stdout( string $string ) {

            if ( !$this->is_enabled ) {

                return;
            }

            $args = func_get_args();
            array_shift($args);
            $this->console->stdout( $string, ...$args );
        }

        /**
         * @inheritdoc
         */
        public function stderr( string $string ) {

            $this->console->stderr( $string );
        }

        /**
         * Formats the line prepend string and returns it.
         * @return string
         */
        protected function formatLinePrepend( ): string {

            $string = $this->prepend_line;
            if ( preg_match( '/<date:([^]])+>/', $string, $matches ) ) {

                $string = str_replace( $matches[0], date( $matches[1] ), $string );
            }

            return $string;
        }

        /**
         * Logs the given string to Console (if logging is enabled and the event is not handled).
         *
         * An event handler can be set on the object to handle logging, i.e.:
         * ```php```
         * $container = new LogContainer(...);
         * $container->on( LogContainer::EVENT_LOG, function ( LogEvent $event ) {
         *
         *      if ( ... ) {
         *
         *          // Text will not be logged on the $container object.
         *          echo $event->getText();
         *          $event->setHandled( true );
         *      }
         * } );
         * ```php```
         *
         * @param string $string - Text to log
         * @param bool $eol - When eol is true, a new line symbol will be added to the end of the string.
         * @param array $styles - Any style constants to be used.
         */
        public function log( string $string, bool $eol = true, array $styles = [] ) {

            if ( !$this->is_enabled ) {

                return;
            }

            $event = new LogEvent( $string, $eol, $styles );
            $this->trigger( self::EVENT_LOG, $event );
            if ( $event->getHandled() === true ) {

                return;
            }

            if ( !$this->is_new_line ) {

                $this->stdout( $string, ...$styles );
            } else {

                $this->stdout( $this->formatLinePrepend() . $string, ...$styles );
                $this->is_new_line = false;
            }

            if ( $eol ) {

                $this->stdout( "\r\n" );
                $this->is_new_line = true;
            }
        }

        /**
         * Logs a text in the set success style.
         * See {@see $styles} about changing defined styles.
         * @param string $string - Text to log
         * @param bool $eol - If true, a new line symbol will be output at the end of the string.
         */
        public function logSuccess( $string = 'OK', $eol = true ) {

            $this->log( $string, $eol, $this->styles[ self::STYLE_SUCCESS ] );
        }

        /**
         * Logs a text in the set error style.
         * See {@see $styles} about changing defined styles.
         * @param string $string - Text to log
         * @param bool $eol - If true, a new line symbol will be output at the end of the string.
         */
        public function logError( $string = 'ERROR', $eol = true ) {

            $this->log( $string, $eol, $this->styles[ self::STYLE_ERROR ] );
        }

        /**
         * Logs a text in the set warning style.
         * See {@see $styles} about changing defined styles.
         * @param string $string - Text to log
         * @param bool $eol - If true, a new line symbol will be output at the end of the string.
         */
        public function logWarning( $string, $eol = true ) {

            $this->log( $string, $eol, $this->styles[ self::STYLE_WARNING ] );
        }
    }