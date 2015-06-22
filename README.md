# Gateway Emulator (gemu)

## Structure

There are 6 folders inside of emulator: app, bin, docker-compose, src, web, vendor. 
Each one of the folder serving its own purpose.

app - templates, wsdls, configurations(soon to be added)
bin - additional utility files such as composer.
docker-compose - docker compose builds and configuration files.
src - project PHP code
vendor - third party dependencies
web - web root directory

## Root of evil

Project php files are located in two folders named Core and Gateway. 
Core folder holds all code that is generic and the gateway folder holds individual implementation 
of each gateway.

### Core or how it works

![Core structure](docs/core_structure.jpg)


## UI

## Javascript

## How to add new gateway

Right now gemu supports two generic types of gateways: SOAP, HTTP. 
Gemu will automatically add logs for each endpoint call so no need to worry about that.
Lets see how to add both of these types.

### Adding SOAP gateway

1. Create folder with gateway name inside of src/Gemu/Gateway.
2. Create two files called Service.php and Emulator.php. 
Extend Service.php from \Gemu\Core\Gateway\EndPoint\Service. 
Extend Gateway.php from \Gemu\Core\Gateway\EndPoint\Emulator\Soap
3. Create folder Soap inside of src/Gemu/Gateway/{gatewayName} and add Handler trait to it. 
This class will handle SOAP queries. 
4. Create mocked wsdl files inside app/wsdl/{gatewayName}
Example. If we want to mock this soap service.
```
<service name="IdentificationApiService">
    <port name="IdentificationApi31" binding="tns:IdentificationApiBinding">
        <soap:address location="http://gemu.app/emulate/Ipx/Identification"/>
    </port>
</service>
```
We need to create wsdl file app/wsdl/{gatewayName}/Identification.wsdl 
and src/Gemu/Gateway/{gatewayName}/Soap/Identification.php. 
**Note: wsdl file and SOAP handler files should be named same**
5. Add operator function into Service.php.
```
/**
 * Class Service
 * @package Gemu\Gateway\SomeGateway
 */
final class Service extends \Gemu\Core\Gateway\EndPoint\Service
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function operators()
    {
        return new JsonResponse(
            [
                '1' => 'DERP',
                '2' => 'HERP',
            ]
        );
    }
}
```
6. Add handler functions to Gateway.php according to wsdl. 
```
<binding name="IdentificationApiBinding" type="tns:IdentificationApiPort">
    <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="createSession">
        <soap:operation soapAction="tns:createSession"/>
        <input>
            <soap:body use="literal"/>
        </input>
        <output>
            <soap:body use="literal"/>
        </output>
    </operation>
</binding>
```

Then we will add **protected** function into src/Gemu/Gateway/{gatewayName}/Soap/Identification.php 
```
    protected function createSession($transactionId, array $request)
```
7. Define getTransactionId and getData functions inside src/Gemu/Gateway/{gatewayName}/Emulator.php
and src/Gemu/Gateway/{gatewayName}/Soap/{soapHanlder}.php.
8. Contemplate existance.

### Adding HTTP gateway

1. Create folder with gateway name inside of src/Gemu/Gateway.
2. Create two files called Service.php and Emulator.php.
Extend Service.php from \Gemu\Core\Gateway\EndPoint\Service. 
Extend Gateway.php from \Gemu\Core\Gateway\EndPoint\Emulator.
3. Add operator function into Service.php.
```
/**
* Class Service
* @package Gemu\Gateway\SomeGateway
*/
final class Service extends \Gemu\Core\Gateway\EndPoint\Service
{
   /**
    * @return \Symfony\Component\HttpFoundation\JsonResponse
    */
   protected function operators()
   {
       return new JsonResponse(
           [
               '1' => 'DERP',
               '2' => 'HERP',
           ]
       );
   }
}
```
4. Add **protected** functions into Emulator that will handle http requests. Ex.
live url: http://somesite.com/scope/doAction?transId=12313&subs=12313
mocked url: http://gemu.app/emulate/SomeGateway/doAction
Inside of src/Gemu/Gateway/{gatewayName}/Emulator.php
```
protected function doAction($transaction_id, array $params)
```
5. Define getTransactionId and getData functions inside src/Gemu/Gateway/{gatewayName}/Emulator.php

## How to run
