# epcvip
Test for EPCVIP

<h3><b>Project Requirements:</b></h3>
 1) php: ^7.1.3 <br>
 2) mysql: ^5.6

<h3><b>Setting Project:</b></h3>
  1) In '.env' file set correct DB params in [DATABASE_URL=mysql://homestead:secret@127.0.0.1:3306/symfony] <br>
  2) In '.env' file Set MAILER_RECIPIENT to recieve emails from application. <br>
  3) In '.env' file set LOG <br>

<h3><b>Authentication:</b></h3>
  Method: POST <br>
  URI:    'api/login' <br>
  BODY:   {'email', 'password'} <br>
  Response: { <br>
    "status": true, <br>
    "auth_token": "Bearer 297b114aa963cae43f5dbdb49c518e91ffc58e866b05..." <br>
  }
 
<h3><b>Example of endpoints:</b></h3>

  Get All customers:<br>
    Method: GET <br>
    URI:    'api/customers'<br>
    HEADERS:   {'Authorization': 'Bearer 297b114aa963cae43f5dbdb49c518e91ffc58e866b05...'}<br>
    
  Get All products:<br>
    Method: GET <br>
    URI:    'api/products'<br>
    HEADERS:   {'Authorization': 'Bearer 297b114aa963cae43f5dbdb49c518e91ffc58e866b05...'}<br>
    
    
 <h3><b>Custom Command:</b></h3> 
 <h4>products:pending</h4>    
 Fetch a list of pending products. List will be sent by email to MAILER_RECIPIENT set in .env file
    
  
  
