<?php
    namespace unique\yii2helperstest\tests;

    use PHPUnit\Framework\TestCase;
    use unique\yii2helpers\components\LogContainer;
    use yii\helpers\Console;

    /**
     * Class LogContainerTest
     *
     * @covers \unique\scraper\LogContainer
     * @package unique\scraperunit\tests
     */
    class LogContainerTest extends TestCase {

        const EOL = "\r\n";

        /**
         * @covers \unique\yii2helpers\components\LogContainer::formatLinePrepend
         */
        public function testFormatLinePrepend() {

            $console = new \unique\yii2helperstest\data\components\Console();
            $container = new LogContainer( $console );

            // @todo change to something smarter for date checking...
            $date = date( 'Y-m-d H:i:s' );
            $container->log( 'test' );
            $this->assertSame( '[' . $date . '] test' . self::EOL, $console->getStdOut() );

            $console->clear();

            // @todo change to something smarter for date checking...
            $container->prepend_line = '[<date:Y-m-d>] ';
            $date = date( 'Y-m-d' );
            $container->log( 'test' );
            $this->assertSame( '[' . $date . '] test' . self::EOL, $console->getStdOut() );

            $console->clear();

            $container->prepend_line = '';
            $container->log( 'test' );
            $this->assertSame( 'test' . self::EOL, $console->getStdOut() );
        }
    }