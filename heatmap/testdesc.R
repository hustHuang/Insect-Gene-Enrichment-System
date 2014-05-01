library("gplots")
#return
#library(bigmemory)
library("fastcluster")
load("/var/www/SGA/heatmap/test.RData")
tt<-data.matrix(test)
#t<-dget("/tmp/matrix.desc")
#matrix<-attach.big.matrix(t)
r=as.integer(GET[["r2"]])-as.integer(GET[["r1"]])+1
c=as.integer(GET[["c2"]])-as.integer(GET[["c1"]])+1
h=16*r
w=16*c

##
#get the map
##

k=tt[GET[["r1"]]:GET[["r2"]],GET[["c1"]]:GET[["c2"]]]
nowtime<-as.integer(Sys.time())
tfn <- paste("/var/www/SGA/data/",nowtime,".png",sep="")
png(filename = tfn,width=w,height=h)
t=heatmap.2(k, col=redgreen(75), scale="row", key=FALSE,keysize=0.00001,dendrogram="none",symkey=FALSE, Rowv=TRUE,Colv=TRUE,distfun = dist ,hclustfun =fastcluster::hclust,density.info="none",trace="none",cexRow=1.0,margins=c(0,0))
dev.off()

##
#get the cluster
##
hcc=as.hclust(t$colDendrogram) 
hcc$height <- (hcc$height - min(hcc$height))/(max(hcc$height) - min(hcc$heigh))+0.1 
ddc <- as.dendrogram(hcc)
tfn1 <- paste("/var/www/SGA/data/row_",as.integer(Sys.time()),".png",sep="")
png(filename = tfn1,height = 160,width = w)
par(mar = c(0, 0, 0, 0))
plot(ddc, axes = FALSE, xaxs = "i", leaflab = "none")
dev.off()
pngname1= strsplit(tfn1,'/')
row_image<-pngname1[[1]][6]

hcr=as.hclust(t$rowDendrogram)
hcr$height <- (hcr$height - min(hcr$height))/(max(hcr$height) - min(hcr$heigh))+0.1 
ddr <- as.dendrogram(hcr)
tfn2<-paste("/var/www/SGA/data/col_",as.integer(Sys.time()),".png",sep="")
png(filename = tfn2,height = h,width = 160)
par(mar = c(0, 0, 0, 0))
plot(ddr, horiz = TRUE, axes = FALSE, yaxs = "i", leaflab = "none")
dev.off()
pngname2= strsplit(tfn2,'/')
col_image<-pngname2[[1]][6] 
##
#get the color key
##
par(mar = c(0, 0, 0, 0))
tfn3 <- paste("/var/www/SGA/data/key_",nowtime,".png",sep="")
png(tfn3,width = 160, height = 160)
x=t$carpet
z <- seq(min(x), max(x), length = length(t$col))
image(z = matrix(z, ncol = 1), col = t$col,xaxt = "n", yaxt = "n")
par(usr = c(0, 1, 0, 1))
lv <- pretty(t$breaks)
xv <- (as.numeric(lv) - min(x))/(max(x) - min(x))
axis(1, at = xv, labels = lv)
mtext(side = 1, "Row Z-Score", line = 5)
           
title("Color Key")
dev.off()
key= strsplit(tfn3,'/')
color_key<-key[[1]][6]
##
#output the html
##
part = strsplit(tfn,'/')
#setContentType("text/html")
#cat('<HTML>\n')
#cat('<head>\n')
#cat('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>\n')
#cat('<link rel="stylesheet" type="text/css" href="../inc/css/heatmap.css" />\n')
#cat('</head>\n')
#cat('<BODY>\n')
cat('<div id="main_container">\n')
cat('<div id="left">\n')
cat('<div id="colorkey">\n')
cat('<img src="./data/')
cat(color_key)
cat('" alt="colImage"/>')
cat('</div>\n<div id="ll">\n')
cat('<img src="./data/')
cat(col_image)
cat('" alt="colImage"/>\n')
cat('</div>\n</div>\n')
cat('<div id="l_">\n')
cat('<div id="tt">\n')
cat('<img src="./data/')
cat(row_image)
cat('" alt="rowImage"/>\n')
cat('</div>\n')
cat('<div id="heatmap"><img src="./data/')
cat(part[[1]][6])
cat('" alt="heatmap"/></div>\n')
cat('<div id="t_"></div>\n</div>\n')
cat('<div id="sr_"><div id="s_"><div class="ss_loading"></div></div>')
cat('<div id="r_"></div></div>\n</div>\n')
##
#output the color key
##

##
#output the hidden genelists
##
cat('<div id="rname">')
cat(colnames(t$carpet))
cat('</div>\n')
cat('<div id="cname">\n')
cat(rownames(t$carpet))
cat('</div>\n')

#cat('<script type="text/javascript" src="../inc/js/lib/jquery-1.6.1.min.js"></script>\n')
#cat('<script type="text/javascript" src="../iipmooviewer2/javascript/raphael.js"></script>\n')
#cat('<script type="text/javascript" src="../inc/js/heatmap.js"></script>\n')
#cat("</BODY>\n</HTML>\n")
