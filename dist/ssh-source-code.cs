using System;
using System.Collections.Generic;
using System.Text;
using Renci.SshNet;

namespace ssh
{
    class Program
    {
        const string HOST = "192.168.1.10";
        const string USER = "pi";
        const string PASSWORD = "blackbox101";
        const string KEYFILE = @"D:\ssh\pk2.ppk";

        static void Main(string[] args)
        {
            var keyFile = new PrivateKeyFile(KEYFILE);
            var keyFiles = new[] { keyFile };

            using (var sftp = new SftpClient( HOST, USER, keyFiles))
            {
                string srcPath = @"D:\webdev\apps\otaku6\dist\api\";
                string destPath = "/var/www/api.otakucms.com/public/";

                string uploadfn = "index.php";

                sftp.Connect();
                uploadFile(sftp, uploadfn, srcPath, destPath);
                srcPath += @"repo\";
                destPath += "repo/";
                uploadFile(sftp, uploadfn, srcPath, destPath);
                uploadfn = "cms.zip";
                uploadFile(sftp, uploadfn, srcPath, destPath);
                sftp.Disconnect();
            }
        }

        static void uploadFile(SftpClient client, string uploadfn, string srcPath, string destPath)
        {
            client.ChangeDirectory(destPath);
            using (var uplfileStream = System.IO.File.OpenRead(srcPath + uploadfn))
            {
                client.UploadFile(uplfileStream, uploadfn, true);
            }
        }
    }
}
