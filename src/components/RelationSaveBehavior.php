<?php
    namespace unique\yii2helpers\components;

    use unique\yii2helpers\exceptions\AbortSavingException;
    use yii\base\Behavior;
    use yii\base\Event;
    use yii\db\ActiveRecord;
    use yii\db\AfterSaveEvent;
    use yii\rest\Serializer;

    /**
     * Allows to save model's relational data, when saving the model itself.
     *
     * For example, if our model is Contact and we want to save ContactTags data alongside the model data,
     * we might have a public attribute `contact_tags` and a relation getter method called `getContactTags()`.
     *
     * By configuring this behaviour as:
     * ```php
     * public function behaviors() {
     *
     *     return array_merge( parent::behaviors(), [
     *         [
     *             'class' => RelationSaveBehavior::class,
     *             'relations' => [
     *                 'contact_tags' => 'contactTags',
     *             ]
     *         ]
     *     ] );
     * }
     * ```php
     *
     * We make sure that all related tags from ContactTags will be loaded when a model is loaded and
     * when saving ContactTag models will be created for every data entry found in `Contact::$contact_tags` attribute.
     *
     * A few things to keep in mind:
     * =============================
     * - Make sure to save data using transactions (for example, use {@see \unique\yii2helpers\traits\TransactionalSaveTrait}
     * - Behavior automatically deletes relational data, that was not found in the set attribute.
     *
     * @var array
     */
    class RelationSaveBehavior extends Behavior {

        /**
         * Specifies which relations should be saved, when saving the model.
         * Keys of the array are the attribute names in the model and values are the relation names.
         * For example, if our model is Contact and we want to save ContactTags alongside the contact,
         * we might have an attribute `contact_tags` and a relation getter method called `getContactTags()`,
         * then by setting this array to:
         * ```php
         * $relations[ 'contact_tags' ] = 'contactTags'
         * ```php
         * We make sure that all related tags from ContactTags will be loaded when a model is loaded and
         * when saving ContactTag models will be created for every data entry found in `Contact::$contact_tags` attribute.
         * @var array
         */
        public array $relations = [
            // model attribute => relation name
        ];

        /**
         * @inheritdoc
         */
        public function events() {

            return [
                ActiveRecord::EVENT_AFTER_FIND => [ $this, 'afterFind' ],
                ActiveRecord::EVENT_AFTER_INSERT => [ $this, 'afterSave' ],
                ActiveRecord::EVENT_AFTER_UPDATE => [ $this, 'afterSave' ],
            ];
        }

        /**
         * Compose a primary key. If model has a composite primary key, this will concat them using '_' symbol.
         * So, a primary key ['taks_id' => 1, 'tag_id' => 50] will become: '1_50'
         *
         * @param array $data_row - All the data
         * @param array $relation_primary_keys - Primary key names
         * @return string
         */
        protected function composeKey( array $data_row, array $relation_primary_keys ): string {

            $primary_key = [];
            foreach ( $relation_primary_keys as $key ) {

                $primary_key[] = $data_row[ $key ] ?? null;
            }

            return implode( '_', $primary_key );
        }

        /**
         * Saves model's relational data found in the specified attribute.
         * @param ActiveRecord $model - The model that is being saved.
         * @param string $relation_name - A Has-Many Relation's name. If the relation method is 'getContactTags()', then name will be 'contactTags'
         * @param string $attribute - Attribute name in the model, where all the relational data is found.
         * @return array
         * @throws AbortSavingException
         * @throws \Throwable
         * @throws \yii\db\StaleObjectException
         */
        public function saveRelation( ActiveRecord $model, string $relation_name, string $attribute ): array {

            $relation = $model->getRelation( $relation_name );
            $model_class = $relation->modelClass;
            $relation_primary_keys = $model_class::primaryKey();

            $existing_data = $relation
                ->indexBy( fn( $row ) => $this->composeKey( $row->toArray(), $relation_primary_keys ) )
                ->all();

            $updated_models = [];

            foreach ( $model->$attribute as $item ) {

                $composite_key = $this->composeKey( $item, $relation_primary_keys );
                if ( !isset( $existing_data[ $composite_key ] ) ) {

                    $related_model = new $model_class;
                } else {

                    $related_model = $existing_data[ $composite_key ];
                    unset( $existing_data[ $composite_key ] );
                }

                $related_model->setAttributes( $item );
                if ( $related_model->getDirtyAttributes() ) {

                    if ( $related_model->isNewRecord ) {

                        foreach ( $relation->link as $rel_attr => $attr ) {

                            $related_model->$rel_attr = $model->getAttribute( $attr );
                        }
                    }

                    if ( !$related_model->save() ) {

                        $model->addErrors( [ $attribute => $related_model->getFirstErrors() ] );
                        throw new AbortSavingException( 'Save aborted' );
                    }
                }

                $updated_models[] = $related_model;
            }

            foreach ( $existing_data as $related_model ) {

                $related_model->delete();
            }

            $model->populateRelation( $relation_name, $updated_models );

            return $updated_models;
        }

        /**
         * Load relational data
         * @param Event $event
         * @return void
         */
        public function afterFind( Event $event ): void {

            /**
             * @var ActiveRecord $sender
             */
            $sender = $event->sender;
            $serializer = new Serializer();

            foreach ( $this->relations as $attribute_name => $relation_name ) {

                $sender->$attribute_name = $serializer->serialize( $sender->$relation_name );
            }
        }

        /**
         * Save relational data
         * @param AfterSaveEvent $event
         * @return void
         * @throws AbortSavingException
         * @throws \Throwable
         * @throws \yii\db\StaleObjectException
         */
        public function afterSave( AfterSaveEvent $event ): void {

            /**
             * @var ActiveRecord $sender
             */
            $sender = $event->sender;

            foreach ( $this->relations as $attribute_name => $relation_name ) {

                $this->saveRelation( $sender, $relation_name, $attribute_name );
            }

            // Let's update the form attribute to reflect the saved data:
            $this->afterFind( $event );
        }
    }