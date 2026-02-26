FROM httpd:2.4

# Copiar configuración del vhost
COPY ./docker/apache/000-default.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf

# Habilitar módulos necesarios en httpd.conf
RUN sed -i \
    -e 's/^#\(Include .*httpd-vhosts.conf\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy_http.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_proxy_balancer.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_lbmethod_byrequests.so\)/\1/' \
    -e 's/^#\(LoadModule .*mod_slotmem_shm.so\)/\1/' \
    /usr/local/apache2/conf/httpd.conf
