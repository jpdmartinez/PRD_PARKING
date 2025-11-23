# PRD_PARKING - Sistema de Gerenciamento de Estacionamento

Sistema de gerenciamento de estacionamento desenvolvido em PHP, seguindo princípios de Clean Architecture e Domain-Driven Design (DDD).

## Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Como Executar](#como-executar)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Arquitetura](#arquitetura)
- [Decisões de Design](#decisões-de-design)
- [Funcionalidades](#funcionalidades)
- [Validações](#validações)

## Sobre o Projeto

Sistema completo para gerenciamento de estacionamento que permite:
- Cadastro de veículos (carros, motos e caminhões)
- Registro de entrada (check-in) e saída (check-out)
- Cálculo automático de tarifas baseado no tempo de permanência
- Geração de relatórios de faturamento

## Autores

**Italo Ricci RA - 1993169**
**João Pedro Belarmino RA - 2007848**
**João Pedro Martinez RA - 1993686**
**José Henrique Ribeiro RA - 1994042**

## Requisitos

- **PHP**: >= 8.0
- **Composer**: Para gerenciamento de dependências
- **SQLite**: Banco de dados

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/jpdmartinez/PRD_PARKING
cd PRD_PARK
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o banco de dados:
```bash
composer migrate
```

Ou execute manualmente:
```bash
php storage/migrate.php
```

O banco de dados SQLite será criado automaticamente em `storage/database.sqlite`.

## Como Executar

### Usando o servidor embutido do PHP (recomendado para desenvolvimento):

```bash
composer serve
```

O sistema estará disponível em: `http://localhost:8000`

### Usando XAMPP:

1. Copie o projeto para a pasta `htdocs` (XAMPP)
2. Acesse via navegador: `http://localhost/PRD_PARK/public/`

## Estrutura do Projeto

```
PRD_PARK/
├── public/                 # Ponto de entrada da aplicação (web root)
│   ├── index.php          # Lista de veículos
│   ├── register.php       # Cadastro de veículos
│   ├── checkin.php        # Check-in de veículos
│   ├── checkout.php       # Check-out de veículos
│   └── reports.php        # Relatórios de faturamento
│
├── src/                    # Código fonte da aplicação
│   ├── Application/       # Camada de aplicação (casos de uso)
│   │   ├── ParkingService.php
│   │   └── ReportService.php
│   │
│   ├── Domain/            # Camada de domínio (regras de negócio)
│   │   ├── Vehicle.php
│   │   ├── VehicleRepository.php
│   │   ├── VehicleValidator.php
│   │   ├── ParkingCalculator.php
│   │   ├── ParkingPolicy.php
│   │   └── Policies/      # Políticas de cálculo por tipo de veículo
│   │       ├── CarPolicy.php
│   │       ├── MotorcyclePolicy.php
│   │       └── TruckPolicy.php
│   │
│   └── Infra/             # Camada de infraestrutura
│       └── SqliteVehicleRepository.php
│
├── storage/               # Armazenamento de dados
│   ├── database.sqlite   # Banco de dados SQLite
│   └── migrate.php       # Script de migração
│
├── vendor/               # Dependências do Composer
├── composer.json         # Configuração do Composer
└── readme.md            # Este arquivo
```

## Arquitetura

O projeto segue uma **Arquitetura em Camadas** com separação clara de responsabilidades:

### Camadas

1. **Domain (Domínio)**
   - Contém as entidades e regras de negócio
   - Independente de frameworks e bibliotecas externas
   - Classes: `Vehicle`, `ParkingPolicy`, `ParkingCalculator`, `VehicleValidator`

2. **Application (Aplicação)**
   - Orquestra os casos de uso
   - Coordena as camadas de domínio e infraestrutura
   - Classes: `ParkingService`, `ReportService`

3. **Infrastructure (Infraestrutura)**
   - Implementações concretas de interfaces do domínio
   - Acesso a dados, APIs externas, etc.
   - Classes: `SqliteVehicleRepository`

4. **Presentation (Apresentação)**
   - Interface com o usuário (HTML/PHP)
   - Localizada em `public/`
   - Responsável por receber requisições e renderizar respostas

## Decisões de Design

### 1. **SRP**
- **Motivo**: Separação clara de responsabilidades, facilitando manutenção e testes
- **Benefício**: Cada camada pode ser testada e modificada independentemente

### 2. **ISP**
- **Motivo**: Abstração do acesso a dados
- **Benefício**: Facilita troca de banco de dados sem alterar a lógica de negócio
- **Implementação**: Interface `VehicleRepository` no domínio, implementação `SqliteVehicleRepository` na infraestrutura

### 3. **LSP**
- **Motivo**: Diferentes tipos de veículos têm diferentes tarifas
- **Benefício**: Fácil adicionar novos tipos de veículo sem modificar código existente
- **Implementação**: Interface `ParkingPolicy` com implementações específicas (`CarPolicy`, `MotorcyclePolicy`, `TruckPolicy`)

### 4. **OCP**
- **Motivo**: Encapsulamento de regras de negócio
- **Benefício**: Garantia de consistência dos dados
- **Exemplo**: Classe `Vehicle` com validações internas

### 5. **PSR-4 Autoloading**
- **Motivo**: Padrão da comunidade PHP
- **Benefício**: Estrutura de namespaces organizada e carregamento automático de classes

## Funcionalidades

### Cadastro de Veículos
- Registro de placa e tipo de veículo
- Validação de placa (formato Mercosul ou antigo)
- Tipos suportados: Carro, Moto, Caminhão
![alt text](prints/image1.PNG)

### Check-in
- Registro de entrada do veículo
- Timestamp automático da entrada
- Validação de duplicidade de check-in
![alt text](prints/image2.PNG)

### Check-out
- Registro de saída do veículo
- Cálculo automático do valor baseado no tempo de permanência
- Tarifas por hora:
  - **Carro**: R$ 5,00/hora
  - **Moto**: R$ 3,00/hora
  - **Caminhão**: R$ 10,00/hora

### Relatórios
- Total de veículos cadastrados
- Faturamento total
- Faturamento por tipo de veículo
- Quantidade de veículos por tipo
![alt text](prints/image3.PNG)

## Validações

### Validação de Placa
A placa deve seguir um dos formatos:
- **Formato Mercosul**: `LLLNLNN` (3 letras, 1 número, 1 letra, 2 números)
  - Exemplo: `ABC1D23`, `ABC-1D23`
- **Formato Antigo**: `LLLNNNN` (3 letras, 4 números)
  - Exemplo: `ABC1234`, `ABC-1234`
![alt text](prints/image4.png)

O hífen é opcional e será ignorado na validação.

### Validação de Datas
- Datas de check-in e check-out devem estar no formato ISO 8601
- Check-out deve ser posterior ao check-in

### Validação de Tipo de Veículo
- Tipos permitidos: `car`, `motorcycle`, `truck`


## Tecnologias Utilizadas

- **PHP 8.0+**: Linguagem de programação
- **Composer**: Gerenciador de dependências
- **SQLite**: Banco de dados
- **PSR-4**: Padrão de autoloading
