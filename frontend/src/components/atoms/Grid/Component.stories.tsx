import clsx from 'clsx'
import React from 'react'
import styles from './styles.module.css'

export default {
  title: 'Organisms/Grid',
}

const Template = () => (
  <div className={styles.grid}>
    <div style={{backgroundColor: "blue"}} />
    <div style={{backgroundColor: "hsl(0, 100%, 70%)", color: "#fff"}}>1</div>
    <div style={{backgroundColor: "hsl(0, 100%, 64%)", color: "#fff"}}>2</div>
    <div style={{backgroundColor: "hsl(0, 100%, 58%)", color: "#fff"}}>3</div>
    <div style={{backgroundColor: "hsl(0, 100%, 52%)", color: "#fff"}}>4</div>
    <div style={{backgroundColor: "hsl(0, 100%, 46%)", color: "#fff"}}>5</div>
    <div style={{backgroundColor: "hsl(0, 100%, 40%)", color: "#fff"}}>6</div>
    <div style={{backgroundColor: "hsl(0, 100%, 34%)", color: "#fff"}}>7</div>
    <div style={{backgroundColor: "hsl(0, 100%, 28%)", color: "#fff"}}>8</div>
    <div style={{backgroundColor: "hsl(0, 100%, 22%)", color: "#fff"}}>9</div>
    <div style={{backgroundColor: "hsl(0, 100%, 16%)", color: "#fff"}}>10</div>
    <div style={{backgroundColor: "hsl(0, 100%, 10%)", color: "#fff"}}>11</div>
    <div style={{backgroundColor: "hsl(0, 100%, 4%)", color: "#fff"}}>12</div>
    <div style={{backgroundColor: "blue"}} />
  </div>
)

const TemplateNoPadding = () => (
  <div className={clsx(styles.grid, styles.noPadding)}>
    <div style={{backgroundColor: "hsl(0, 100%, 70%)", color: "#fff"}}>1</div>
    <div style={{backgroundColor: "hsl(0, 100%, 64%)", color: "#fff"}}>2</div>
    <div style={{backgroundColor: "hsl(0, 100%, 58%)", color: "#fff"}}>3</div>
    <div style={{backgroundColor: "hsl(0, 100%, 52%)", color: "#fff"}}>4</div>
    <div style={{backgroundColor: "hsl(0, 100%, 46%)", color: "#fff"}}>5</div>
    <div style={{backgroundColor: "hsl(0, 100%, 40%)", color: "#fff"}}>6</div>
    <div style={{backgroundColor: "hsl(0, 100%, 34%)", color: "#fff"}}>7</div>
    <div style={{backgroundColor: "hsl(0, 100%, 28%)", color: "#fff"}}>8</div>
    <div style={{backgroundColor: "hsl(0, 100%, 22%)", color: "#fff"}}>9</div>
    <div style={{backgroundColor: "hsl(0, 100%, 16%)", color: "#fff"}}>10</div>
    <div style={{backgroundColor: "hsl(0, 100%, 10%)", color: "#fff"}}>11</div>
    <div style={{backgroundColor: "hsl(0, 100%, 4%)", color: "#fff"}}>12</div>
  </div>
)

export const Default = Template.bind({})
export const NoPadding = TemplateNoPadding.bind({})
