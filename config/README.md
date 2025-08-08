# 🚀 Deployment System for PHP Docker App

Acest sistem oferă o soluție completă pentru gestionarea și deployment-ul aplicației PHP în medii multiple (development și production).

## 📋 Caracteristici

- ✅ **Multi-environment support** - Development și Production
- ✅ **SSL certificate management** - Automat pentru ambele medii
- ✅ **Apache configuration** - Generat din template-uri
- ✅ **WebSocket configuration** - Configurare automată
- ✅ **Docker integration** - Build și deployment automat
- ✅ **Backup system** - Rollback și restore
- ✅ **Health monitoring** - Verificare status și performanță
- ✅ **Colorized output** - Interfață prietenoasă

## 🏗️ Structura sistemului

```
config/
├── env                    # Configurația curentă (generată)
├── env.dev               # Configurația pentru development
├── env.prod              # Configurația pentru production
├── apache/
│   ├── dev.conf          # Template Apache pentru dev
│   └── prod.conf         # Template Apache pentru prod
├── websocket/
│   ├── dev.conf          # Template WebSocket pentru dev
│   └── prod.conf         # Template WebSocket pentru prod
├── ssl/
│   ├── dev/              # Certificat SSL pentru dev
│   └── prod/             # Certificat SSL pentru prod
├── backups/               # Backup-uri automate
├── deploy.sh              # Script principal de deployment
├── rollback.sh            # Script de rollback și backup
├── monitor.sh             # Script de monitoring
├── switch-env.sh          # Comutare între medii
├── generate-configs.sh    # Generare configurații
├── setup-ssl.sh           # Setup SSL certificates
├── test-config.sh         # Testare configurație
└── Makefile               # Comenzi rapide
```

## 🚀 Comenzi rapide

### Deployment

```bash
# Deploy la development
./config/deploy.sh dev

# Deploy la production
./config/deploy.sh prod

# Deploy cu specificarea explicită
./config/deploy.sh deploy dev
./config/deploy.sh deploy prod

# Verificare status
./config/deploy.sh status

# Validare mediu
./config/deploy.sh validate dev
./config/deploy.sh validate prod
```

### Rollback și Backup

```bash
# Creare backup
./config/rollback.sh backup dev

# Listare backup-uri
./config/rollback.sh list

# Restore din backup
./config/rollback.sh restore config/backups/env.backup.20231201_143022

# Rollback la mediu specific
./config/rollback.sh rollback dev
./config/rollback.sh rollback prod
```

### Monitoring

```bash
# Verificare completă de sănătate
./config/monitor.sh

# Verificare Docker
./config/monitor.sh docker

# Verificare servicii
./config/monitor.sh services

# Verificare SSL
./config/monitor.sh ssl

# Verificare spațiu disk
./config/monitor.sh disk

# Generare raport
./config/monitor.sh report
```

### Comenzi Makefile

```bash
# Din directorul config/
make dev              # Switch la development
make prod             # Switch la production
make status           # Status curent
make generate         # Generare configurații
make setup            # Setup SSL
make clean            # Curățare fișiere generate
make dev-setup        # Setup complet development
make prod-setup       # Setup complet production
```

## 🔧 Configurare inițială

### 1. Setup SSL certificates

```bash
./config/setup-ssl.sh
```

Acest script va:
- Crea directoarele SSL pentru ambele medii
- Genera certificat self-signed pentru development
- Genera certificat self-signed pentru production
- Seta permisiunile corecte

### 2. Configurare mediu

```bash
# Pentru development
./config/switch-env.sh dev

# Pentru production
./config/switch-env.sh prod
```

### 3. Generare configurații

```bash
./config/generate-configs.sh
```

## 🌍 Medii disponibile

### Development (dev)
- **Domain**: localhost
- **Ports**: 80 (HTTP), 443 (HTTPS), 8080 (WebSocket)
- **SSL**: Self-signed certificate
- **Debug**: Enabled
- **Logging**: Verbose

### Production (prod)
- **Domain**: www.estimatorapp.site
- **Ports**: 80 (HTTP), 443 (HTTPS), 8080 (WebSocket)
- **SSL**: Production certificate
- **Debug**: Disabled
- **Security**: Enhanced headers
- **Logging**: Error level only

## 🔐 SSL Configuration

### Development
- **Certificate**: `config/ssl/dev/localhost.crt`
- **Private Key**: `config/ssl/dev/localhost.key`
- **Subject**: `/C=RO/ST=Bucharest/L=Bucharest/O=Localhost/OU=IT/CN=localhost`
- **Validity**: 365 zile

### Production
- **Certificate**: `config/ssl/prod/estimator.crt`
- **Private Key**: `config/ssl/prod/estimator.key`
- **Subject**: `/C=RO/ST=Bucharest/L=Bucharest/O=Estimator/OU=IT/CN=www.estimatorapp.site`
- **Validity**: 365 zile

## 📁 Fișiere generate

După rularea scripturilor, următoarele fișiere vor fi generate în directorul rădăcină:

- `apache-ssl.conf` - Configurația Apache pentru mediul curent
- `websocket.conf` - Configurația WebSocket pentru mediul curent
- `ssl/` - Certificatul SSL activ pentru mediul curent

## 🐳 Docker Integration

Sistemul se integrează automat cu Docker:

```bash
# Deploy complet cu Docker
./config/deploy.sh dev    # Va opri, rebuild și porni containerele

# Rollback cu Docker
./config/rollback.sh rollback dev    # Va restaura și reporni containerele
```

## 📊 Monitoring și Health Checks

### Verificări automate
- ✅ Status containere Docker
- ✅ Conectivitate servicii (HTTP, HTTPS, WebSocket)
- ✅ Spațiu disk disponibil
- ✅ Validitate certificat SSL
- ✅ Log-uri și erori
- ✅ Resurse sistem

### Generare rapoarte
```bash
./config/monitor.sh report
```

Generează un raport complet cu:
- Informații despre mediu
- Status Docker
- Resurse sistem
- Log-uri recente

## 🔄 Workflow de deployment

### Development
1. `./config/deploy.sh dev`
2. Sistemul comută la development
3. Generează configurațiile
4. Rebuild și porneste containerele Docker
5. Verifică status-ul

### Production
1. `./config/deploy.sh prod`
2. Sistemul comută la production
3. Generează configurațiile
4. Rebuild și porneste containerele Docker
5. Verifică status-ul

### Rollback
1. `./config/rollback.sh rollback [dev|prod]`
2. Creează backup al stării curente
3. Comută la mediul țintă
4. Regenera configurațiile
5. Repornește containerele

## 🚨 Troubleshooting

### Probleme comune

#### Certificat SSL expirat
```bash
./config/setup-ssl.sh
```

#### Configurație Apache invalidă
```bash
./config/generate-configs.sh
```

#### Containere Docker nu pornesc
```bash
./config/monitor.sh docker
docker-compose logs
```

#### Probleme de permisiuni
```bash
chmod +x config/*.sh
```

### Log-uri și debugging

```bash
# Verificare log-uri Docker
docker-compose logs

# Verificare log-uri Apache
docker exec -it php-docker-app-web-1 tail -f /var/log/apache2/error.log

# Verificare status servicii
./config/monitor.sh services
```

## 📚 Dependințe

- **Docker** - Pentru containere
- **docker-compose** - Pentru orchestrare
- **OpenSSL** - Pentru certificat SSL
- **envsubst** - Pentru substituirea variabilelor (din pachetul gettext)
- **curl** - Pentru verificări HTTP
- **nc** - Pentru verificări port (netcat)

### Instalare dependințe macOS
```bash
brew install gettext
```

## 🔒 Securitate

### Development
- Debug enabled
- Logging verbose
- Self-signed certificates
- Headers de securitate de bază

### Production
- Debug disabled
- Logging minimal
- Certificat SSL valid
- Headers de securitate avansate
- HSTS enabled
- Content Security Policy

## 📞 Suport

Pentru probleme sau întrebări:

1. Verifică log-urile: `./config/monitor.sh logs`
2. Verifică status-ul: `./config/deploy.sh status`
3. Testează configurația: `./config/test-config.sh`
4. Generează raport: `./config/monitor.sh report`

## 🎯 Best Practices

1. **Întotdeauna creează backup** înainte de schimbări
2. **Testează în development** înainte de production
3. **Verifică status-ul** după fiecare deployment
4. **Monitorizează regulat** sănătatea aplicației
5. **Păstrează backup-urile** pentru cel puțin 30 de zile
6. **Documentează schimbările** în configurație

## 🚀 Deployment Checklist

### Înainte de deployment
- [ ] Backup al mediului curent
- [ ] Validare configurație
- [ ] Verificare dependințe
- [ ] Testare în development

### După deployment
- [ ] Verificare status containere
- [ ] Testare conectivitate servicii
- [ ] Verificare log-uri
- [ ] Generare raport de sănătate

### Pentru production
- [ ] Verificare certificat SSL
- [ ] Testare securitate
- [ ] Verificare performanță
- [ ] Monitorizare continuă
