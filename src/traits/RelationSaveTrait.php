<?php
    namespace unique\yii2helpers\traits;

    use unique\yii2helpers\exceptions\AbortSavingException;
    use yii\db\ActiveRecord;

    /**
     * Should be used on a Model class, allows easy relational data saving.
     */
    trait RelationSaveTrait {

        /**
         * Saves related data.
         * 1. Iterate through $relation_model_data
         * 2. Check if data primary ID is found in $existing_models, if so use it, else create a new model.
         * 3. Call setAttributes() on a new/existing model with the data taken from $relation_model_data
         * 4. Assign $foreign_key for a new/existing model
         * 5. Try saving, if it fails add a new attribute to $relation_model_data[]['errors'] with all save errors.
         * 6. If all models saved correctly, delete expired $existing_models models and return
         *    Else, throw AbortSavingException with modified $relation_model_data.
         *
         * If $error_field is provided, all relational model errors will also be set on the main ($this) model.
         * Template variables can be used, for example: "custom_field.[key].[attribute]", where:
         * - $key - the key from $relation_model_data array, that was being saved;
         * - $attribute - the relational data attribute, violating it's model rule.
         *
         * @param ActiveRecord[] $existing_models - An associated array of Models, using primary keys of related data that is currently in DB.
         * @param array $relation_model_data - An array of data, to be assigned to models.
         * @param string $relation_class - A class of relational model
         * @param string|array $foreign_key - Foreign keys in the relation model, i.e.: 'part_id' or [ 'part_id' => 'id' ]
         * @param string|null $error_field - If provided, this key will be used to when calling $this->addError().
         * @param string|null $model_key - The attribute to use as a primary key. If not specified $this->primaryKey() is used.
         *                                 Aggregate keys can be used by separating them with comma (needs to be tested, lol :D)
         * @throws AbortSavingException
         * @throws \Throwable
         * @throws \yii\db\StaleObjectException
         */
        public function saveRelation( $existing_models, $relation_model_data, $relation_class, $foreign_key, ?string $error_field, ?string $model_key = null ) {

            $has_errors = false;
            if ( !is_array( $foreign_key ) ) {

                $foreign_key = [ $foreign_key => 'id' ];
            }

            foreach ( $relation_model_data ?? [] as $key => $model_data ) {

                /**
                 * @var ActiveRecord $model
                 */
                $model = new $relation_class();
                if ( $model_key === null ) {

                    $primary_key = implode( ',', $model->primaryKey() );
                } else {

                    $primary_key = $model_key;
                }

                if ( isset( $model_data[ $primary_key ] ) && isset( $existing_models[ $model_data[ $primary_key ] ] ) ) {

                    $model = $existing_models[ $model_data[ $primary_key ] ];
                }

                if ( !$model->isNewRecord ) {

                    unset( $existing_models[ $model_data[ $primary_key ] ] );
                } else {

                    $primary_keys = $this->getPrimaryKey( true );
                    foreach ( $foreign_key as $foreign_k => $primary_k ) {

                        $model->$foreign_k = $primary_keys[ $primary_k ];
                        unset( $model_data[ $foreign_k ] );
                    }
                }

                $model->setAttributes( $model_data );

                if ( !$model->save() ) {

                    $relation_model_data[ $key ]['errors'] = $model->getErrors();
                    if ( $error_field !== null ) {

                        foreach ( $model->getErrors() as $attribute => $errors ) {

                            foreach ( $errors as $error ) {

                                $error_key = str_replace( [ '[key]', '[attribute]' ], [ $key, $attribute ], $error_field );
                                $this->addError( $error_key, $error );
                            }
                        }
                    }
                    $has_errors = true;
                }
            }

            if ( $has_errors ) {

                AbortSavingException::fromRelationSave( $relation_model_data );
            }

            foreach ( $existing_models as $model ) {

                $model->delete();
            }
        }
    }