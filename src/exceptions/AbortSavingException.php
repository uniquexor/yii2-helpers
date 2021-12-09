<?php
    namespace unique\yii2helpers\exceptions;

    /**
     * An exception thrown when a relational data could not be saved.
     */
    class AbortSavingException extends \Exception {

        public $relation_model_data;

        public static function fromRelationSave( $relation_model_data ) {

            $exception = new self();
            $exception->relation_model_data = $relation_model_data;

            throw $exception;
        }
    }