<?php
    namespace uniquexor\yii2helpers\validators;

    /**
     * Additional functionality for yii2 DateValidator:
     * - Allows {@see $format} to be an array to validate any of the given formats.
     *
     * @property string|string[] $format
     */
    class DateValidator extends \yii\validators\DateValidator {

        /**
         * @inheritdoc
         *
         * In addition to default behaviour, checks if {@see $format} is an array and attempts to validate any of the given formats.
         */
        protected function parseDateValue($value) {

            if ( is_array( $this->format ) ) {

                foreach ( $this->format as $format ) {

                    $res = $this->parseDateValueFormat( $value, $format );
                    if ( $res !== false ) {

                        return $res;
                    }
                }

                return false;
            } else {

                return $this->parseDateValueFormat( $value, $this->format );
            }
        }
    }