pipeline {
    agent any

    environment {
        GIT_BRANCH   = 'main'
        DEPLOY_PATH  = '/var/www/Elite_cafe_backend'
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
    }

    stages {
        stage('Clone Repository') {
            steps {
                git branch: "${GIT_BRANCH}",
                    credentialsId: 'elite_cafe_github',
                    url: 'https://github.com/aiswaryanekkanti/Elite_cafe_backend.git'
            }
        }

        stage('Copy to Deploy Path') {
            steps {
                script {
                    sh """
                        sudo rm -rf ${DEPLOY_PATH}
                        sudo mkdir -p ${DEPLOY_PATH}
                        sudo cp -r . ${DEPLOY_PATH}
                    """
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
