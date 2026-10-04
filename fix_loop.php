<?php
$f = "resources/js/Pages/Products/Show.vue";
$c = file_get_contents($f);

$old = '<tr v-for="(spec, index) in product.specs" :key="index" :class="index % 2 === 0 ? \'bg-white\' : \'bg-gray-50\'">
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-700 w-1/3">{{ spec.label }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ spec.value }}</td>
                                    </tr>';

$new = '<tr v-for="(value, key) in product.specs" :key="key" class="even:bg-gray-50 odd:bg-white">
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 w-1/3">{{ key }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ value }}</td>
                                    </tr>';

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Done\n";
