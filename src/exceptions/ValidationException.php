<?php
    namespace unique\yii2helpers\exceptions;

    /**
     * Class ValidationException.
     *
     * Allows to easily throw an Exception with the errors from the model's save() operation.
     * For example:
     * ```php```
     * if ( !$model->save() ) {
     *
     *     throw ValidationException::setFromModel( $model->getErrors() );
     * }
     * ```php```
     *
     * After that, by caling {@see getMessage()} method, one can get a fully formated HTML with the errors.
     */
    class ValidationException extends \Exception {

        /**
         * Model's errors.
         * @var array
         */
        public $errors = [];

        /**
         * Creates a ValidationException from Model's errors.
         * Also set's {@see ValidationException::$errors}
         *
         * @param array $errors - Result from {@see Model::getErrors()}
         * @param bool $as_html - True if errors need to be separated by '<br />', false for - "\n"
         * @return ValidationException
         */
        public static function setFromModel( $errors = [], $as_html = true ) {

            $model = new self( self::getErrorsAsString( $errors, $as_html ? '<br />' : "\n" ) );
            $model->errors = $errors;
            return $model;
        }

        /**
         * Takes all the errors from the model and converts it to string.
         * Very easy to use in Ajax queries.
         *
         * For example:
         * if ( !$model->save() ) {
         *
         *     echo json_encode( [ 'error' => ValidationException::getErrorsAsString( $model->getErrors(), '<br />' ) ] );
         * }
         *
         * @param array $errors
         * @param string $separator
         * @return string
         */
        public static function getErrorsAsString( $errors, $separator = "\n" ) {

            $return = array();
            foreach ( $errors as $error ) {

                if ( is_array( $error ) ) {

                    $return[] = self::getErrorsAsString( $error, $separator );
                } else {

                    $return[] = $error;
                }
            }

            return implode( $separator, $return );
        }
    }