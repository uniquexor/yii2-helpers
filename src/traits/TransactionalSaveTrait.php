<?php
    namespace unique\yii2helpers\traits;

    use unique\yii2helpers\exceptions\AbortSavingException;

    /**
     * Adds transactional save functionality
     * All save operations will be executed in a transaction.
     */
    trait TransactionalSaveTrait {

        public function save( $runValidation = true, $attributeNames = null ) {

            $transaction = null;
            if ( \Yii::$app->db->getTransaction() === null ) {

                $transaction = \Yii::$app->db->beginTransaction();
            }

            $res = false;

            try {

                $res = parent::save( $runValidation, $attributeNames );
                if ( $transaction ) {

                    if ( $res ) {

                        $transaction->commit();
                    } else {

                        $transaction->rollBack();
                    }
                }
            } catch ( \Throwable $exception ) {

                if ( $transaction ) {

                    $transaction->rollBack();
                }

                if ( !( $exception instanceof AbortSavingException ) ) {

                    throw $exception;
                }
            }

            return $res;
        }
    }