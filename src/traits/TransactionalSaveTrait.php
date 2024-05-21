<?php
    namespace unique\yii2helpers\traits;

    use unique\yii2helpers\exceptions\AbortSavingException;

    /**
     * Adds transactional save functionality
     * All save operations will be executed in a transaction.
     * If a transaction has already started, does not start a new one.
     */
    trait TransactionalSaveTrait {

        public function save( $runValidation = true, $attributeNames = null ) {

            $transaction = null;
            if ( \Yii::$app->db->getTransaction() === null ) {

                $transaction = \Yii::$app->db->beginTransaction();
            }

            $is_new_record = $this->isNewRecord;
            $primary_keys = $this->getPrimaryKey( true );
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

                    if ( $is_new_record ) {

                        // If saving failed, we need to restore model's state
                        $this->isNewRecord = $is_new_record;
                        $this->setAttributes( $primary_keys, false );
                    }
                }

                if ( !( $exception instanceof AbortSavingException ) ) {

                    throw $exception;
                }
            }

            return $res;
        }
    }