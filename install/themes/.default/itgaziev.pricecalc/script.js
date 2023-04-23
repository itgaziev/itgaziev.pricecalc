var ITGazievUpdatePrice = function (args) {
    this.ajaxUpdate = (options) => {
        var _this = this;

        BX.ajax({
            url : '/bitrix/admin/itgaziev.pricecalc_ajax.php',
            data : {
                options : options,
            },
            method: 'POST',
            dataType: 'json',
            timeout: 3600,
            async: true,
            processData : true,
            scriptsRunFirst : true,
            emulateOnload : true,
            start: true,
            onsuccess : (response) => {
                let procent = response.procent;
                if(procent > 100) procent = 100;
                if(response.action == 'success') {
                    console.log('end import');
                    $('.myBar').css('width', procent + '%');
                } else {
                    let newoptions = {...options, ...response}
                    setTimeout(() => {
                        _this.ajaxUpdate(newoptions)
                    }, 5000)
                    $('.myBar').css('width', procent + '%');
                    console.log(newoptions)
                    console.log(response)
                }
            },
            onfailure: () => {}
        })
    }
}