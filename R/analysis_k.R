
#genes <- toString("YAL009W,YAL010C,YAL011W,YAL013W,YAL015C,YAL019W,YAL020C,YAL024C,YAL026C,YAL029C,YAL030W,YAL034W-A,YAL040C,YAL048C,YAL054C,YAL055W,YAR002W,YAR003W,YAR007C,YAR014C,YAR019C,YAR031W,YAR033W,YBL002W,YBL003C,YBL006C,YBL007C,YBL008W,YBL009W,YBL016W,YBL031W,YBL032W,YBL034C,YBL035C,YBL038W,YBL050W,YBL052C,YBL058W,YBL059C-A,YBL063W,YBL078C,YBL079W,YBL080C,YBL084C,YBL088C,YBL097W,YBL105C,YBR009C,YBR010W,YBR044C,YBR048W,YBR059C,YBR060C,YBR073W,YBR080C,YBR087W,YBR088C,YBR095C,YBR103W,YBR105C,YBR107C,YBR108W,YBR109C,YBR111W-A,YBR112C,YBR114W")
#tologytype <- toString("BP")
genes<-POST[["g"]]
cutoff <- as.numeric(POST[["c"]])
#representation <- toString("over")
nowtime<-as.integer( Sys.time())
tfn <- paste("/tmp/table_",nowtime,".txt",sep="")
segene <- unlist(strsplit(genes, split=","))
len <- length(segene)
genelist <- readLines("/var/www/SGA/R/result.orf")
params1 = new ("KEGGHyperGParams", geneIds=segene,universeGeneIds = genelist, annotation = "org.Sc.sgd.db", categoryName="KEGG",pvalueCutoff= cutoff)
over = hyperGTest(params1)
sover<-summary(over)
write.table(sover,file=tfn,row.names=FALSE,col.names=FALSE,quote=FALSE,sep = "\t")
cat(nowtime)

