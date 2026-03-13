resource "null_resource" "export_env_vars" {
  triggers = {
    access_key   = minio_iam_service_account.s3_api_keys.access_key
    secret_key   = minio_iam_service_account.s3_api_keys.secret_key
    forum_bucket = minio_s3_bucket_policy.forum.bucket
    users_bucket = minio_s3_bucket_policy.users.bucket
  }

  provisioner "local-exec" {
    command = <<-EOT
      export AWS_ACCESS_KEY_ID="${minio_iam_service_account.s3_api_keys.access_key}"
      export AWS_SECRET_ACCESS_KEY="${minio_iam_service_account.s3_api_keys.secret_key}"
      export AWS_FORUM_BUCKET="${minio_s3_bucket_policy.forum.bucket}"
      export AWS_USERS_BUCKET="${minio_s3_bucket_policy.users.bucket}"
    EOT
  }
}
