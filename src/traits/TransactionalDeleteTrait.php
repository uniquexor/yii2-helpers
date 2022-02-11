<?php
    namespace unique\yii2helpers\traits;

    /**
     * Adds transactional delete functionality
     * All delete operations will be executed in a transaction.
     * If a transaction has already started, does not start a new one.
     */
    trait TransactionalDeleteTrait {

        public function delete() {

            $transaction = null;
            if ( \Yii::$app->db->getTransaction() === null ) {

                $transaction = \Yii::$app->db->beginTransaction();
            }

            $res = false;

            try {

                $res = parent::delete();
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

                throw $exception;
            }

            return $res;
        }
    }