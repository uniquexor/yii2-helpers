<?php
    namespace app\components;

    use yii\base\Component;
    use yii\base\Model;
    use yii\base\StaticInstanceTrait;

    /**
     * Helps with serializing an array of Models without checking request object for expand and fields options.
     */
    class ModelsSerializer extends Component {

        use StaticInstanceTrait;

        /**
         * Serializes an array of Models, passing given $fields and $expand option.
         * @param Model[] $models
         * @param array $fields
         * @param array $expand
         * @return array
         */
        public function serialize( array $models, array $fields = [], array $expand = [] ): array {

            $list = [];
            foreach ( $models as $key => $model ) {

                $list[ $key ] = $model->toArray( $fields, $expand );
            }

            return $list;
        }
    }