# yii-throttlesfilter

###Yii filter to throttle user request.
###usage in controller
    public function filters()
    {
        return [
            [
                'pathTo/ThrottlesFilter',
                'limit' => 20,
            ],
        ];
    }
    
###Have fun!
