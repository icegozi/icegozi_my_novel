FROM nginx:1.27-alpine

RUN apk add --no-cache curl

# Remove default configuration
RUN rm -rf /etc/nginx/conf.d/*

# Copy custom enterprise Nginx configurations
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www

# Expose HTTP and HTTPS
EXPOSE 80 443

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

CMD ["nginx", "-g", "daemon off;"]
