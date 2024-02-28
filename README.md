# sf6-yousign-sample

A sample application with Symfony 6 and Yousign Api integration


## Features
- Create PDF file with Twig template and DomPDF library
- Make Yousign signature resquest on generated document
- Webhook endpoint called by Yousign after signature processed by signer


## Tech Stack
- Symfony 6.4
- PHP 8.3
- PostgreSQL 16
- Docker with compose plugin


## How to use
1. Clone the repo
``` bash
git clone https://github.com/SBordier44/sf6-yousign-sample
```


2. Install dependencies
``` bash
composer install
```


3. Start the dev server
``` bash
docker compose up -d
symfony serve
```


4. Configure application
```
Edit .env file to customize configuration and append your Yousign API Key
```


5. Generate contract and PDF for signature request
```
1. Go to https://localhost:8000/contract
2. Create a new contract
3. Click "show" link (end line) at last contract created to display details
4. Click "Send To Signature" to start Yousign signature process
5. After signed document in Yousign, check /var/storage to get signed_contract_*.pdf returned by Yoursign and original pdf
```


## Contributing
Pull requests are welcome. 😁
