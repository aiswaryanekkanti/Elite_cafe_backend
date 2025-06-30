pipeline {
    agent any

    environment {
        GIT_BRANCH      = 'main'
        DEPLOY_PATH     = '/var/www/Elite_cafe_backend'
        GIT_CREDENTIALS = credentials('elite_cafe_github')
        GIT_URL         = "https://${GIT_CREDENTIALS_USR}:${GIT_CREDENTIALS_PSW}@github.com/aiswaryanekkanti/Elite_cafe_backend.git"
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
    }

    stages {

        stage('Clone Repository') {
            steps {
                script {
                    if (fileExists("${DEPLOY_PATH}/.git")) {
                        echo "📁 Repository already exists. Pulling latest changes..."
                        sh """
                            cd ${DEPLOY_PATH}
                            git reset --hard
                            git clean -fd
                            git pull origin ${GIT_BRANCH}
                        """
                    } else {
                        echo "🆕 Cloning repository to deployment path..."
                        sh "git clone -b ${GIT_BRANCH} ${GIT_URL} ${DEPLOY_PATH}"
                    }
                }
            }
        }

        stage('Ensure .env File') {
            steps {
                sh """
                    if [ ! -f ${DEPLOY_PATH}/.env ]; then
                        cp ${DEPLOY_PATH}/.env.example ${DEPLOY_PATH}/.env
                    fi
                """
            }
        }

        stage('Install Dependencies') {
            steps {
                sh "cd ${DEPLOY_PATH} && composer install --no-interaction --prefer-dist --optimize-autoloader"
            }
        }

        stage('Set File Permissions') {
            steps {
                sh """
                    cd ${DEPLOY_PATH}
                    sudo chown -R www-data:www-data .
                    sudo chmod 664 .env || true
                    sudo chown www-data:www-data .env || true
                    sudo find storage -type d -exec chmod 775 {} \\;
                    sudo find storage -type f -exec chmod 664 {} \\;
                    sudo find bootstrap/cache -type d -exec chmod 775 {} \\;
                    sudo find bootstrap/cache -type f -exec chmod 664 {} \\;
                """
            }
        }

        stage('Generate App Key') {
            steps {
                sh "cd ${DEPLOY_PATH} && php artisan key:generate"
            }
        }

        stage('Laravel Optimization') {
            steps {
                sh """
                    cd ${DEPLOY_PATH}
                    php artisan config:clear
                    php artisan cache:clear
                    php artisan route:clear
                    php artisan view:clear
                    php artisan optimize:clear
                """
            }
        }
    }

    post {
        success {
            echo '✅ Deployment completed successfully!'
        }
        failure {
            echo '❌ Deployment failed. Check the build logs.'
        }
    }
}
