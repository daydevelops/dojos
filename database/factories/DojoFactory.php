<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Dojo;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class DojoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dojo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word . " " . $this->faker->word,
            'user_id' => User::factory(),
            'location' => $this->getLocationDefault(),
            'price' => "$" . rand(20, 40) . "/month",
            'contact' => $this->faker->sentence,
            'classes' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'facebook' => $this->faker->url,
            'twitter' => $this->faker->url,
            'youtube' => $this->faker->url,
            'instagram' => $this->faker->url,
        ];
    }

    public function getLocationDefault() {
        return <<<EOD
            {"address_components":[{"long_name":"Pippy Park","short_name":"Pippy Park","types":["neighborhood","political"]},{"long_name":"St. John's","short_name":"St. John's","types":["locality","political"]},{"long_name":"Division No. 1","short_name":"Division No. 1","types":["administrative_area_level_2","political"]},{"long_name":"Newfoundland and Labrador","short_name":"NL","types":["administrative_area_level_1","political"]},{"long_name":"Canada","short_name":"CA","types":["country","political"]},{"long_name":"A1B","short_name":"A1B","types":["postal_code_prefix","postal_code"]}],"adr_address":"Pippy Park, <span class=\"locality\">St. John&#39;s</span>, <span class=\"region\">NL</span> <span class=\"postal-code\">A1B</span>, <span class=\"country-name\">Canada</span>","formatted_address":"Pippy Park, St. John's, NL A1B, Canada","geometry":{"location":{"lat":47.57758459999999,"lng":-52.7481119},"viewport":{"south":47.56167591847196,"west":-52.7770327998772,"north":47.58758206292447,"east":-52.72799898078423}},"icon":"https://maps.gstatic.com/mapfiles/place_api/icons/v1/png_71/geocode-71.png","name":"Pippy Park","photos":[{"height":2952,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/110502731778329754336\">craig simons</a>"],"width":5248},{"height":3456,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/108125599094607058721\">Jocelyn Kelland</a>"],"width":4608},{"height":800,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/103181518584657205879\">Grand Concourse Authority</a>"],"width":1200},{"height":3024,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/115610086825142003791\">Bahram Zolfaghari</a>"],"width":4032},{"height":2620,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/115994784121826027744\">Wendi Jiang</a>"],"width":4656},{"height":1052,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/117625531785105161599\">The Fluvarium</a>"],"width":1080},{"height":411,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/117625531785105161599\">The Fluvarium</a>"],"width":1080},{"height":3492,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/100740907542350345817\">Jennifer Tulk</a>"],"width":4656},{"height":2620,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/115994784121826027744\">Wendi Jiang</a>"],"width":4656},{"height":1908,"html_attributions":["<a href=\"https://maps.google.com/maps/contrib/117625531785105161599\">The Fluvarium</a>"],"width":4032}],"place_id":"ChIJwZBYS3ikDEsRKsbC2Nv5boI","reference":"ChIJwZBYS3ikDEsRKsbC2Nv5boI","types":["neighborhood","political"],"url":"https://maps.google.com/?q=Pippy+Park,+St.+John%27s,+NL+A1B,+Canada&ftid=0x4b0ca4784b5890c1:0x826ef9dbd8c2c62a","utc_offset_minutes":-210,"vicinity":"St. John's","html_attributions":[],"utc_offset_minutes":-210}
        EOD;
    }
}
