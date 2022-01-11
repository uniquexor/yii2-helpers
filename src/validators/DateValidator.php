<?php
    namespace unique\yii2helpers\validators;

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

                $formats = $this->format;

                foreach ( $formats as $format ) {

                    $this->format = $format;
                    $res = parent::parseDateValue( $value );
                    if ( $res !== false ) {

                        $this->format = $formats;
                        return $res;
                    }
                }

                $this->format = $formats;
                return false;
            } else {

                return parent::parseDateValue( $value );
            }
        }
    }