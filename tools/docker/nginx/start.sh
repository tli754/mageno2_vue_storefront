#!/usr/bin/env sh
certs_dir='/.lego/certificates/'
third_party_certs_dir='/etc/ssl/whitedonkey/'
ca_location=${CA_LOCATION:-'/ca/ca_crt.pem'}
ca_key_location=${CA_KEY_LOCATION:-'/ca/ca_key.pem'}
acme_server=${ACME_SERVER:-'https://acme-v02.api.letsencrypt.org/directory'}

wdca_domains=$(cat /etc/nginx/conf.d/* | grep "#@wdca" | awk '{print $2}')

if [ ! -f ${ca_location} ]; then
    # OpenSSL supports env vars in config, but LibreSSL does not. So, we copy the config and parse it.
    mkdir -p /ca/ca.conf
    cp /ca.openssl.conf /ca/ca.conf
    domain='ca.whitedonkey.local' /usr/local/bin/ep -v /ca/ca.conf

	openssl genrsa -out ${ca_key_location} 4096
#	chmod 400 ${ca_key_location}
	openssl req -key ${ca_key_location} -new -x509 -days 7300 -sha256 -extensions v3_ca -out ${ca_location} \
	  -subj "/C=NZ/ST=Auckland/L=Auckland/O=White Donkey Ltd/CN=ca.whitedonkey.local" -config /ca/ca.conf/ca.openssl.conf
fi

for domain in $wdca_domains
do
    if [ ! -f ${certs_dir}${domain}.key ]; then
        echo $domain
        mkdir -p ${certs_dir}
        # OpenSSL supports env vars in config, but LibreSSL does not. So, we copy the config and parse it.
        cp /ca.openssl.conf ${certs_dir}${domain}.conf
        domain=$domain /usr/local/bin/ep -v ${certs_dir}${domain}.conf

        openssl req -nodes -new -keyout ${certs_dir}${domain}.key -out ${certs_dir}${domain}.csr -nodes -config ${certs_dir}${domain}.conf
        # If we use `openssl ca` rather than `openssl x509`, we'd be able to use a lot of existing config,
        # and retain extensions from the CSR, but we don't want to bother with managing a database.

 	    openssl x509 -req -days 365 -sha256 -set_serial "0x$(openssl rand -hex 16)" -in ${certs_dir}${domain}.csr -CA ${ca_location} -CAkey ${ca_key_location} -out ${certs_dir}${domain}.crt -extensions req_ext -extfile ${certs_dir}${domain}.conf
    fi
done

/usr/sbin/nginx -g "daemon off;"