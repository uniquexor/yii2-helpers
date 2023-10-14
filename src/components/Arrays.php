<?php
    namespace unique\yii2helpers\components;

    use Closure;
    use Exception;

    /**
     * Array helper.
     * @todo translate docblocks
     */
    class Arrays {

        /**
         * Priskiria naują reikšmę nurodytai masyvo šakai, arba ją pakoreguoja.
         *
         * Pvz nr. 1:
         * Turime masyvą: [ 914 => [ 5 => [ 'komisinis' => 1, 'suma' => 2 ] ]
         * Iškviečiame: old\modules\Arrays::assignToArrayByArrayKeys( $array, [ 914, 5], ['proc' => 0.5] );
         * Rezultatas: [ 914 => [ 5 => [ 'komisinis' => 1, 'suma' => 2, 'proc' => 0.5 ] ]
         *
         * Pvz nr. 2:
         * Iškviečiame: old\modules\Arrays::assignToArrayByArrayKeys( $array, [ 914, 5, 'komisinis'], function ( $e ) { return $e * 2; } );
         * Rezultatas: [ 914 => [ 5 => [ 'komisinis' => 2, 'suma' => 2 ] ]
         *
         * Pvz nr. 3:
         * Iškviečiame:
         * old\modules\Arrays::assignToArrayByArrayKeys( $array, [ 201401, null ], 5 );
         * old\modules\Arrays::assignToArrayByArrayKeys( $array, [ 201401, null ], 10 );
         * Rezultatas: [ 201401 => [ 5, 10 ]
         *
         *
         * Galimos $value reikšmės:
         *   - funkcija: ji gaus reikšmę, kurią reikia koreguoti ir turės gražinti naują
         *   - masyvas, jeigu redaguojama reikšmė irgi yra masyvas, tuomet, nauja reikšmė bus priappend'inta, priešingu atveju, tiesiog priskirta.
         *   - skalerinis dydis - reikšmė bus priskirta
         *
         * @param array $array - Reikšmių masyvas. Jeigu null, tai bus append'inama.
         * @param array $keys - Sąrašas elementų, kurie nurodo kelią iki šakos, kurią keisime
         * @param mixed $value - Nauja reikšmė
         * @param bool|true $previous_key_exists - Vidiniam naudojimui
         */
        public static function assignToArrayByArrayKeys( &$array, $keys, $value, $previous_key_exists = true ) {

            if ( $keys ) {

                $key = array_shift( $keys );
                if ( !isset( $array[ $key ] ) && !is_null( $key ) ) {

                    $array[ $key ] = array();
                    $previous_key_exists = false;
                } elseif ( is_null( $key ) ) {

                    $keys = array();
                }

                if ( is_null( $key ) ) {

                    Arrays::assignToArrayByArrayKeys( $array[], $keys, $value, $previous_key_exists );
                } else {

                    Arrays::assignToArrayByArrayKeys( $array[ $key ], $keys, $value, $previous_key_exists );
                }
            } else {

                if ( is_callable( $value ) ) {

                    $array = call_user_func( $value, $array );
                } elseif ( is_array( $array ) && ( $previous_key_exists || !empty( $array ) ) ) {

                    if ( is_array( $value ) ) {

                        $array = array_merge( $array, $value );
                    } else {

                        $array[] = $value;
                    }
                } else {

                    $array = $value;
                }
            }
        }

        /**
         * Paima reikšmę iš masyvo pagal nurodytą kelią.
         * @param array $array
         * @param string $path
         * @return mixed|null
         */
        public static function getArrayValue( $array, string $path ) {

            if ( !$path ) {

                return $array;
            }

            $keys = explode( '.', $path );
            $key = array_shift( $keys );

            if ( !isset( $array[ $key ] ) ) {

                return null;
            }

            if ( $keys ) {

                return self::getArrayValue( $array[ $key ], implode( '.', $keys ) );
            } else {

                return $array[ $key ];
            }
        }

        /**
         * Pagal nurodytą kelią sukuria asociatyvų masyvą.
         * $path kintamasis nusako kokia turėtų būti struktūra naujojo masyvo. Nauja dimensija atskiriama `.` simboliu.
         * Jeigu $path bus tuščias, bus gražintas $array masyvas.
         * $path taip pat gali būti Closure, tokiu atveju jai bus perduoti du parametrai: 1) masyvo item'as konvertuotas į masyvą 2) nepaliestas masyvo item'as
         * Pvz.:
         * <code>
         *      $array = [ 0 => [ 'id' => 1, 'value' => 'Value 1' ], 1 => [ 'id' => 3, 'value' => 'Value 2' ] ]
         *      var_dump( old\modules\Arrays::makeArrayByPath( $array, 'id' ) );
         * </code>
         *
         * Šis kodas gražintų naują vienmatį asociatyvų masyvą pagal id lauką:
         * [
         *      1 => [ 'id' => 1, 'value' => 'Value 1' ],
         *      3 => [ 'id' => 3, 'value' => 'Value 2' ]
         * ]
         *
         * Jeigu būtų paduota 'id.value', būtų gražinama dvimatis masyvas, kurio pirmas lygmuo būtų `id` reikšmė, antras lygmuo - `value` reikšmė.
         *
         * $path gali užsibaigti `.`, tuomet visi elementai bus append'inami. Nurodytu atveju, iškvietus
         * <code>
         *      var_dump( old\modules\Arrays::makeArrayByPath( $array, 'id.' ) );
         * </code>
         * Šis kodas gražintų naują vienmatį asociatyvų masyvą pagal id lauką:
         * [
         *      1 => [
         *          [ 'id' => 1, 'value' => 'Value 1' ]
         *      ],
         *      3 => [
         *          [ 'id' => 3, 'value' => 'Value 2' ]
         *      ]
         * ]
         *
         * $path taip pat gali būti Closure, pvz.:
         * <code>
         *      $array = [['key' => 1, 'value' => 'vienas'], ['key' => 2, 'value' => 'du'], ['key' => 3, 'value' => 'trys'] ];
         *      $res = old\modules\Arrays::makeArrayByPath(
         *          $array,
         *          function ( $obj ) {
         *              return $obj['key'] * 10;
         *          },
         *          'value'
         *      );
         * </code>
         * Tokiu atveju, rezultatas būtų:
         * [
         *      10 => 'vienas', 20 => 'du', 30 => 'trys'
         * ]
         *
         * Jeigu $value_field:
         *      - string: nurodo iš kurio atributo paimti reikšmę
         *      - array: masyvo reikšmės - atributų pavadinimai, kurių reikšmes reikia paimt. Pvz.:
         *          makeArrayByPath( [ 'id' => 1, 'name' => 2, 'test' => true ], 'id', ['id', 'name'] );
         *          Rezultatas:
         *          [ 1 => ['id' => 1, 'name' => 2 ] ]
         *      - null: imti visas reikšmes, nieko nekeičiant
         *      - closure: iškviesti perduotą Closure, kuriai parametrais perduodama:
         *          pirmu - eilutės reikšmės konvertuotos į masyvą,
         *          antru - nekonvertuotos (priklausys nuo originalių duomenų, ar bus objektas, ar masyvas)
         *
         * @param array|object[] $array
         * @param string|Closure $path - Taškais atskirti naujojo masyvo keys
         * @param string|array|null|Closure $value_field - Nurodo kokie duomenys turi būti masyvo reikšmės.
         * @throws Exception
         * @return array
         */
        public static function makeArrayByPath( array $array, $path, $value_field = null ) {

            if ( !$path ) {

                return $array;
            }

            $data = array();
            if ( $path == '.' ) {

                if ( is_null( $value_field ) ) {

                    // Jeigu path yra taškas, o $value_field - neperduotas, tuomet pernumeruojam masyvą
                    return array_values( $array );
                } else {

                    // Jeigu path yra taškas, o $value_field - perduotas, tuomet neskaidom path'o, o tiesiog paliekam jį kaip tuščią elementą.
                    $count = 1;
                    $path = array( null );
                }
            } elseif ( !( $path instanceof Closure ) ) {

                // Išskaidom path'ą į masyvą
                $path = explode( '.', $path );
                if ( ( $count = count( $path ) ) == 0 ) {

                    return $array;
                }
            }

            foreach ( $array as $entry_obj ) {

                // Jeigu perduotas objektų masyvas, pasidarom jį į masyvų masyvą.

                // Nusistatom kokia bus reikšmė (ar visas masyvas, ar konkretus jo elementas)
                $res = $entry_obj;
                if ( is_string( $value_field ) ) {

                    $res = is_array( $entry_obj ) ? $entry_obj[ $value_field ] : $entry_obj->$value_field;
                } else {

                    if ( $value_field instanceof Closure ) {

                        $res = call_user_func( $value_field, $entry_obj );
                    } elseif ( is_array( $value_field ) ) {

                        // Šita operacija - labai brangi laiko prasme, "kainuoja" iki 10x daugiau:
                        $entry = Arrays::getArray( $entry_obj );
                        $res = array_intersect_key( $entry, array_flip( $value_field ) );
                    }
                }

                if ( $path instanceof Closure ) {

                    $key = call_user_func( $path, $entry_obj );
                    $data[ $key ] = $res;
                } else {

                    $keys = array();
                    foreach ( $path as $path_item ) {

                        if ( !$path_item ) {

                            $path_item = null;
                        }

                        $key = null;
                        if ( $path_item !== null ) {

                            if ( is_array( $entry_obj ) ) {

                                $key = ( $entry_obj[ $path_item ] === null ) ? '' : $entry_obj[ $path_item ];
                            } else {

                                $key = ( $entry_obj->$path_item === null ) ? '' : $entry_obj->$path_item;
                            }
                        }
                        $keys[] = $key;
                    }

                    Arrays::assignToArrayByArrayKeys( $data, $keys, $res );
                }
            }

            return $data;
        }


        /**
         * Jeigu perduotas objektas, konvertuoja jį į masyvą, jeigu array, tai jį patį ir gražina, jeigu kas nors kitka - gražina tuščią masyvą
         * @param {array|object} $obj
         * @return array
         */
        public static function getArray( $obj ) {

            if ( is_array( $obj ) ) {

                return $obj;
            } elseif ( is_object( $obj ) ) {

                if ( method_exists( $obj, 'toArray' ) && is_callable( array( $obj, 'toArray' ) ) ) {

                    return $obj->toArray();
                } else {

                    return get_object_vars( $obj );
                }
            } else {

                return array();
            }
        }


        /**
         * Paima visus Modelio validavimo error'us ir paverčia juos vienu string'u.
         * Labai patogu naudoti Ajax'inėse užklausose.
         *
         * Naudojimo pavyzdys:
         * if ( !$model->save() ) {
         *
         *     echo json_encode( [ 'error' => Arrays::getErrorsAsString( $model->getErrors(), '<br />' ) ] );
         *     Yii->$app()->end();
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

                    foreach ( $error as $error_item ) {

                        $return[] = $error_item;
                    }
                } else {

                    $return[] = $error;
                }
            }

            return implode( $separator, $return );
        }
    }