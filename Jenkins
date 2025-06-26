pipeline {
    agent any

    environment {
        // Basic app config
        APP_NAME      = 'Elite_cafe_backend'
        DEPLOY_DIR    = "/var/www/${APP_NAME}"

        // Git config
        GIT_URL       = 'https://github.com/aiswaryanekkanti/Elite_cafe_backend.git'
        GIT_BRANCH    = 'main'
        GIT_CREDENTIALS_ID = 'elite_cafe_github'
    }

    options {
        timeout(time: 30, unit: 'MINUTES') // Prevent stuck builds
    }

    stages {
        stage('Clone or Update Repository') {
            steps {
                dir("/var/www") {
                    script {
                        def exists = fileExists("${DEPLOY_DIR}/.git")
                        if (exists) {
                            echo "🔄 Repository exists. Pulling latest changes..."
                            sh """
                                cd ${DEPLOY_DIR}
                                git fetch origin
                                git reset --hard origin/${GIT_BRANCH}
                                git clean -fd
                            """
                        } else {
                            echo "🆕 Cloning repository..."
                            sh "git clone -b ${GIT_BRANCH} https://${GIT_CREDENTIALS_USR}:${GIT_CREDENTIALS_PSW}@github.com/aiswaryanekkanti/Elite_cafe_backend.git ${DEPLOY_DIR}"
                        }
                    }
                }
            }
        }

        stage('Ensure .env File') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh """
                    if [ ! -f .env ]; then
                        cp .env.example .env
                        echo "✅ .env file created"
                    else
                        echo "ℹ️ .env file already exists — skipped"
                    fi
                    """
                }
            }
        }

        stage('Install Dependencies') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --no-dev --optimize-autoloader'
                }
            }
        }

        stage('Generate App Key') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php artisan key:generate'
                }
            }
        }

        stage('Laravel Optimization') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh """
                    php artisan config:clear
                    php artisan cache:clear
                    php artisan route:clear
                    php artisan view:clear
                    php artisan optimize:clear

                    php artisan config:cache
                    php artisan route:cache
                    php artisan view:cache
                    """
                }
            }
        }

        stage('Fix Permissions') {
            steps {
                sh """
                sudo chown -R www-data:www-data ${DEPLOY_DIR}
                sudo chmod -R 775 ${DEPLOY_DIR}/storage
                sudo chmod -R 775 ${DEPLOY_DIR}/bootstrap/cache
                """
            }
        }
    }

    post {
        success {
            echo '✅ Laravel deployment completed successfully!'
        }
        failure {
            echo '❌ Deployment failed. Check the build logs.'
        }
    }

    environment {
        // Injects credentials into GIT_CREDENTIALS_USR and GIT_CREDENTIALS_PSW
        GIT_CREDENTIALS = credentials('elite_cafe_github')
    }
}
