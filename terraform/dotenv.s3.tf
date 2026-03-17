# For creating the .env.s3 file for local development
resource "local_sensitive_file" "env" {
    filename = "${path.module}/../.env.s3"
    content  = <<-EOT
        AWS_ACCESS_KEY_ID=${minio_iam_service_account.s3_api_keys.access_key}
        AWS_SECRET_ACCESS_KEY=${minio_iam_service_account.s3_api_keys.secret_key}
        AWS_URL=http://127.0.0.1:9000
        AWS_ENDPOINT=http://127.0.0.1:9000
        AWS_USE_PATH_STYLE_ENDPOINT=true

        AWS_FORUM_BUCKET=${minio_s3_bucket_policy.forum.bucket}
        AWS_USERS_BUCKET=${minio_s3_bucket_policy.users.bucket}
    EOT
}
