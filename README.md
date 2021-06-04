# Form helper
Laravel form helper; automatically generate AJAX forms, validations and much more all with a single fluent and fully customizable definition.

## Installation
Install it with composer
`composer require plokko/form-helper`

To use the Vue component add it in your main `app.js` file by directly referencing it:
```javascript
//...
Vue.component('form-helper', require('../../vendor/plokko/form-helper/resources/components/FormHelper').default);
```
Or by referencing the published component:

publish the Vue components with `php artisan vendor:publish --provider="Plokko\FormHelper\FormHelperServiceProvider" --tag=components` then add it to your `app.js` file:
```javascript
//...
Vue.component('form-helper', require('./vendor/plokko/form-helper/FormHelper').default);
```

## Use

Define your form in the controller
```php
//...
use Plokko\FormHelper\FormHelper;

class TestController extends Controller
{
    //...
    public function example(Request $request){
        $form = new FormHelper();
        //Just an example model as data source
        $data = User::first();
        
        $form
            // Fill field values
            ->fill($data)
            // Specify form action and method
            ->action(route('users.edit',1),'patch')

            // Field definition:
            ->field('name')
                ->required(true)
                ->min(3)
                ->label('User name')
                //All the field with a prepending ":" will be evaluated as Javascript, usefull for defining functions
                ->attr(':rule','[v=>!!v || "Campo richiesto",v=> (!!v && v.length>=3)|| "Lunghezza minima 3ch"]')
            
            ->field('email')
                ->type('email')
                ->label('E-mail')
                ->required(true)

            ->field('password')
                ->type('password')
                ->label('Password')
                ->attr('min',3)

            ->field('filetest')
                ->type('file')
                ->label('File upload')
            
            //Redefine all the field labels using the specified translation array;
            // for example the trans id "users.fields.name" will be assigned as a label to "name"
            ->labelsFromTrans('users.fields')
            ;

        return view('your.blade.file',compact('form'));
    }
}
```

Then use the defined form in your blade file:
```blade
    <!-- Renders the form-->
    {{ $form->render() }}
    
    <!-- Render the form component; same as before but allows customization -->
    <form-helper 
        {{ $form->renderFormAttr() }} 
        @submit="onSubmit"
        @error="onError" 
        >
        @verbatim
            <!-- Custom slot for a single field (by field name) -->
            <template v-slot:field.email="{field,item}">
                <label for="customfield">{{field.label}}</label>
                <input 
                    type="email" 
                    v-model="item.value" 
                    :name="field.name" 
                    id="customfield"
                    />
            </template>
           
            <!-- Custom slot for field type -->
            <template v-slot:type.file="{field,item}">
                <label>{{field.label}}
                    <input 
                        type="file" 
                        @change="item.onFileChange"
                        v-bind="field"
                        />
                </label>
            </template>
        @endverbatim
    </form-helper>
    
```

Continue to the [wiki](https://github.com/plokko/form-helper/wiki) for additional informations
